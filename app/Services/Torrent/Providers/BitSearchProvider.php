<?php

namespace App\Services\Torrent\Providers;

use App\Services\Torrent\TorrentProviderInterface;
use App\Services\Torrent\TorrentResult;
use Exception;
use Illuminate\Support\Facades\Http;

/**
 * Provider para BitSearch.to — DHT search engine (sucessor do SolidTorrents).
 *
 * Faz scraping HTML da página de busca, pois não há API JSON pública.
 */
class BitSearchProvider implements TorrentProviderInterface
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? 'https://bitsearch.to';
        $this->timeout = (int) ($config['timeout'] ?? 10);
    }

    public function getName(): string
    {
        return 'bitsearch';
    }

    public function search(string $query): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get("{$this->baseUrl}/search", [
                    'q' => $query,
                ]);

            if (! $response->successful()) {
                return [];
            }

            $body = $response->body();
            $results = [];

            // Dividir por blocos de resultado (cada card começa com "flex items-start justify-between")
            $blocks = preg_split('/<div class="flex items-start justify-between">/', $body);

            foreach ($blocks as $block) {
                // Extrair título do h3
                if (! preg_match('/<h3[^>]*>.*?<a[^>]*>([^<]+)<\/a>/s', $block, $titleMatch)) {
                    continue;
                }
                $title = trim($titleMatch[1]);

                // Extrair magnet link (com HTML entities)
                if (! preg_match('/href="magnet:[^"]+"/', $block, $magnetMatch)) {
                    continue;
                }
                $magnet = html_entity_decode($magnetMatch[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $magnet = str_replace('href="', '', $magnet);
                $magnet = rtrim($magnet, '"');

                // Extrair tamanho
                $size = '';
                if (preg_match('/<i class="fas fa-download"><\/i>\s*<span>(\d+\.?\d*\s*(GB|MB|TB|KB))<\/span>/s', $block, $sizeMatch)) {
                    $size = trim($sizeMatch[1]);
                }

                // Extrair seeders
                $seeders = 0;
                if (preg_match('/text-green-600.*?<span class="font-medium">(\d+)<\/span>\s*<span>seeders<\/span>/s', $block, $seedersMatch)) {
                    $seeders = (int) $seedersMatch[1];
                }

                // Extrair leechers
                $leechers = 0;
                if (preg_match('/text-red-600.*?<span class="font-medium">(\d+)<\/span>\s*<span>leechers<\/span>/s', $block, $leechersMatch)) {
                    $leechers = (int) $leechersMatch[1];
                }

                // Extrair categoria
                $category = null;
                if (preg_match('/<i class="fas fa-file[^"]*"><\/i>\s*<span>([^<]+)<\/span>/s', $block, $catMatch)) {
                    $category = trim($catMatch[1]);
                }

                $results[] = new TorrentResult(
                    title: $title,
                    magnet: $magnet,
                    size: $size,
                    seeders: $seeders,
                    leechers: $leechers,
                    source: $this->getName(),
                    url: '',
                    category: $category,
                );
            }

            return $results;
        } catch (Exception $e) {
            return [];
        }
    }
}
