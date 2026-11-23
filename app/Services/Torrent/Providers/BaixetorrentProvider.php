<?php

namespace App\Services\Torrent\Providers;

use App\Services\Torrent\TorrentProviderInterface;
use App\Services\Torrent\TorrentResult;
use Exception;
use Illuminate\Support\Facades\Http;

/**
 * Provider para Baixetorrent.org — busca de torrents via scraping.
 *
 * Faz scraping HTML da página de busca do site.
 */
class BaixetorrentProvider implements TorrentProviderInterface
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? 'https://www.baixetorrent.org';
        $this->timeout = (int) ($config['timeout'] ?? 10);
    }

    public function getName(): string
    {
        return 'baixetorrent';
    }

    public function search(string $query): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get("{$this->baseUrl}/", [
                    's' => urlencode($query),
                ]);

            if (! $response->successful()) {
                return [];
            }

            $body = $response->body();
            $results = [];

            // Extrair todos os links de torrent da página de resultados
            // O baixetorrent usa <a> com href para download e título do torrent
            $links = [];
            if (preg_match_all('/<a[^>]*href="([^"]+)"[^>]*>([^<]+)<\/a>/i', $body, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $href = $match[1];
                    $title = trim(html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                    // Filtrar apenas links relevantes (magnet ou .torrent)
                    if (str_contains($href, 'magnet:') || str_contains($href, '.torrent')) {
                        $links[] = ['href' => $href, 'title' => $title];
                    }
                }
            }

            // Extrair informações de cada resultado
            // Dividir a página por cards de resultado
            $blocks = preg_split('/<div class="[^"]*torrent[^"]*"/i', $body);

            foreach ($blocks as $block) {
                // Extrair título
                if (! preg_match('/<a[^>]*class="[^"]*title[^"]*"[^>]*>([^<]+)<\/a>/i', $block, $titleMatch)) {
                    continue;
                }
                $title = trim(html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                // Extrair magnet link
                $magnet = '';
                if (preg_match('/href="(magnet:[^"]+)"/i', $block, $magnetMatch)) {
                    $magnet = $magnetMatch[1];
                } elseif (preg_match('/href="([^"]+)"[^>]*class="[^"]*magnet[^"]*"/i', $block, $magnetMatch)) {
                    $magnet = 'magnet:?xt=urn:btih:' . basename($magnetMatch[1]);
                }

                if (empty($magnet)) {
                    continue;
                }

                // Extrair tamanho
                $size = '';
                if (preg_match('/(\d+\.?\d*\s*(GB|MB|TB|KB))/i', $block, $sizeMatch)) {
                    $size = trim($sizeMatch[0]);
                }

                // Extrair seeders/leechers se disponível
                $seeders = 0;
                $leechers = 0;
                if (preg_match('/(\d+)\s*seeders?/i', $block, $seedersMatch)) {
                    $seeders = (int) $seedersMatch[1];
                }
                if (preg_match('/(\d+)\s*leechers?/i', $block, $leechersMatch)) {
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

            return $results;
        } catch (Exception $e) {
            return [];
        }
    }
}
