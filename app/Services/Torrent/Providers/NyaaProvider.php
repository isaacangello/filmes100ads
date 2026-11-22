<?php

namespace App\Services\Torrent\Providers;

use App\Services\Torrent\TorrentProviderInterface;
use App\Services\Torrent\TorrentResult;
use Exception;
use Illuminate\Support\Facades\Http;

/**
 * Provider para Nyaa — tracker público de torrents.
 *
 * Usa a API JSON do Nyaa para buscar resultados por termo.
 */
class NyaaProvider implements TorrentProviderInterface
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? 'https://nyaa.si/api?';
        $this->timeout = (int) ($config['timeout'] ?? 5);
    }

    public function getName(): string
    {
        return 'nyaa';
    }

    public function search(string $query): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl, [
                    'f' => 'json',
                    'q' => $query,
                ]);

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();
            $results = [];

            // Nyaa retorna array com estrutura: [0] => { 'nyaa' => { 'items' => [...] } }
            if (is_array($data) && isset($data[0]['nyaa']['items'])) {
                foreach ($data[0]['nyaa']['items'] as $item) {
                    $results[] = new TorrentResult(
                        title: (string) ($item['title'] ?? ''),
                        magnet: (string) ($item['download'] ?? ''),
                        size: (string) ($item['size'] ?? ''),
                        seeders: (int) ($item['seeders'] ?? 0),
                        leechers: (int) ($item['leechers'] ?? 0),
                        source: $this->getName(),
                        url: (string) ($item['link'] ?? ''),
                        category: ! empty($item['category']) ? (string) $item['category'] : null,
                    );
                }
            }

            return $results;
        } catch (Exception $e) {
            return [];
        }
    }
}
