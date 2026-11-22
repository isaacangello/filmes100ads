<?php

namespace App\Services\Torrent\Providers;

use App\Services\Torrent\TorrentProviderInterface;
use App\Services\Torrent\TorrentResult;
use Exception;
use Illuminate\Support\Facades\Http;

/**
 * Provider para SolidTorrents — API pública de torrents.
 *
 * Usa a API JSON do site para buscar resultados por termo.
 */
class SolidTorrentsProvider implements TorrentProviderInterface
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? 'https://solidtorrents.to/api';
        $this->timeout = (int) ($config['timeout'] ?? 5);
    }

    public function getName(): string
    {
        return 'solidtorrents';
    }

    public function search(string $query): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl, [
                    'q' => $query,
                    'type' => 'json',
                ]);

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();
            $results = [];

            // A API retorna um array de torrents com campos: title, magnet, size, seeders, etc.
            if (is_array($data)) {
                foreach ($data as $item) {
                    $results[] = new TorrentResult(
                        title: (string) ($item['title'] ?? $item['name'] ?? ''),
                        magnet: (string) ($item['magnet'] ?? ''),
                        size: $this->formatSize((int) ($item['size'] ?? 0)),
                        seeders: (int) ($item['seeders'] ?? 0),
                        leechers: (int) ($item['leechers'] ?? 0),
                        source: $this->getName(),
                        url: (string) ($item['url'] ?? ''),
                        category: ! empty($item['category']) ? (string) $item['category'] : null,
                    );
                }
            }

            return $results;
        } catch (Exception $e) {
            // Em caso de erro, retornar array vazio — o service trata o fallback
            return [];
        }
    }

    /**
     * Formata bytes para string legível.
     */
    protected function formatSize(int $bytes): string
    {
        if ($bytes <= 0) {
            return '';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
