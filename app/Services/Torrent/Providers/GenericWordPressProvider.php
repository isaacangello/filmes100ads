<?php

namespace App\Services\Torrent\Providers;

use App\Services\Torrent\TorrentProviderInterface;
use App\Services\Torrent\TorrentResult;
use Exception;
use Illuminate\Support\Facades\Http;

/**
 * Provider genérico para sites de torrents baseados em WordPress.
 *
 * Funciona com sites como Baixetorrent e similares que usam WordPress
 * com plugins de torrent. O usuário pode digitar qualquer URL no console ADM.
 */
class GenericWordPressProvider implements TorrentProviderInterface
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(string $baseUrl, int $timeout = 10)
    {
        // Normalizar URL: remover trailing slash e paths
        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        $this->baseUrl = $scheme . '://' . $host;
        $this->timeout = $timeout;
    }

    public function getName(): string
    {
        $parsed = parse_url($this->baseUrl);
        return 'custom:' . ($parsed['host'] ?? 'unknown');
    }

    public function search(string $query): array
    {
        try {
            // WordPress usa ?s= para busca
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($this->baseUrl, [
                    's' => urlencode($query),
                ]);

            if (! $response->successful()) {
                return [];
            }

            $body = $response->body();
            $results = [];

            // Estratégia 1: Buscar magnet links diretamente
            $magnets = [];
            if (preg_match_all('/(magnet\?:[^"\'<\s]+)/i', $body, $magnetMatches)) {
                $magnets = array_unique($magnetMatches[1]);
            }

            // Estratégia 2: Buscar blocos de resultados (WordPress post entries)
            // Divide por <article>, <div class="post", <li class="post", etc.
            $blocks = preg_split('/<(article|div|li)[^>]*class="[^"]*(?:post|torrent|item|entry)[^"]*"/i', $body);

            foreach ($blocks as $block) {
                // Extrair título do bloco
                $title = '';
                if (preg_match('/<a[^>]*class="[^"]*(?:title|entry-title|post-title|name)[^"]*"[^>]*>([^<]+)<\/a>/i', $block, $titleMatch)) {
                    $title = trim(html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                } elseif (preg_match('/<h[1-6][^>]*>([^<]+)<\/h[1-6]/i', $block, $titleMatch)) {
                    $title = trim(html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }

                if (empty($title)) {
                    continue;
                }

                // Extrair magnet link do bloco
                $magnet = '';
                if (preg_match('/(magnet\?:[^"\'<\s]+)/i', $block, $magnetMatch)) {
                    $magnet = $magnetMatch[1];
                }

                if (empty($magnet)) {
                    continue;
                }

                // Extrair tamanho
                $size = '';
                if (preg_match('/(\d+\.?\d*\s*(GB|MB|TB|KB))/i', $block, $sizeMatch)) {
                    $size = trim($sizeMatch[0]);
                }

                // Extrair seeders/leechers
                $seeders = 0;
                $leechers = 0;
                if (preg_match('/(\d+)\s*(?:seeders?|verde?|green)/i', $block, $seedersMatch)) {
                    $seeders = (int) $seedersMatch[1];
                }
                if (preg_match('/(\d+)\s*(?:leechers?|vermelho?|red)/i', $block, $leechersMatch)) {
                    $leechers = (int) $leechersMatch[1];
                }

                $results[] = new TorrentResult(
                    title: $title,
                    magnet: $magnet,
                    size: $size,
                    seeders: $seeders,
                    leechers: $leechers,
                    source: $this->getName(),
                    url: '',
                    category: null,
                );
            }

            // Estratégia 3: Se não encontrou blocos, usar todos os magnets encontrados
            // com o termo de busca como título
            if (empty($results) && !empty($magnets)) {
                foreach ($magnets as $magnet) {
                    // Tentar extrair info_hash do magnet para usar como parte do título
                    $infoHash = '';
                    if (preg_match('/xt=urn:btih:([a-fA-F0-9]+)/i', $magnet, $ihMatch)) {
                        $infoHash = substr($ihMatch[1], 0, 8);
                    }

                    $results[] = new TorrentResult(
                        title: $query . ' (' . $infoHash . ')',
                        magnet: $magnet,
                        size: '',
                        seeders: 0,
                        leechers: 0,
                        source: $this->getName(),
                        url: '',
                        category: null,
                    );
                }
            }

            // Estratégia 4: Se ainda não encontrou nada, tentar buscar na página de cada resultado
            if (empty($results)) {
                // Extrair URLs de posts da página de busca
                $postUrls = [];
                if (preg_match_all('/href="([^"]+)"[^>]*>([^<]*(?:download|torrent|magnet|baixar)[^<]*)<\/a>/i', $body, $postMatches, PREG_SET_ORDER)) {
                    foreach ($postMatches as $match) {
                        $url = $match[1];
                        if (filter_var($url, FILTER_VALIDATE_URL) && !str_contains($url, 'magnet:')) {
                            $postUrls[] = $url;
                        }
                    }
                }

                // Tentar buscar em cada URL (limitado a 5 para não demorar)
                $postUrls = array_slice($postUrls, 0, 5);
                foreach ($postUrls as $url) {
                    $subResults = $this->scrapePostPage($url, $query);
                    $results = array_merge($results, $subResults);
                }
            }

            return $results;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Faz scraping de uma página individual de post/torrent.
     */
    protected function scrapePostPage(string $url, string $searchTerm): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get($url);

            if (! $response->successful()) {
                return [];
            }

            $body = $response->body();
            $results = [];

            // Extrair título da página
            $title = $searchTerm;
            if (preg_match('/<title>([^<]+)<\/title>/i', $body, $titleMatch)) {
                $title = trim(html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }

            // Extrair magnet link
            $magnet = '';
            if (preg_match('/(magnet\?:[^"\'<\s]+)/i', $body, $magnetMatch)) {
                $magnet = $magnetMatch[1];
            }

            if (empty($magnet)) {
                return [];
            }

            // Extrair tamanho
            $size = '';
            if (preg_match('/(\d+\.?\d*\s*(GB|MB|TB|KB))/i', $body, $sizeMatch)) {
                $size = trim($sizeMatch[0]);
            }

            $results[] = new TorrentResult(
                title: $title,
                magnet: $magnet,
                size: $size,
                seeders: 0,
                leechers: 0,
                source: $this->getName(),
                url: $url,
                category: null,
            );

            return $results;
        } catch (Exception $e) {
            return [];
        }
    }
}
