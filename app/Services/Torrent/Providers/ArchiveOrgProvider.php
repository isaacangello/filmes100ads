<?php

namespace App\Services\Torrent\Providers;

use App\Services\Torrent\TorrentProviderInterface;
use App\Services\Torrent\TorrentResult;
use Exception;
use Illuminate\Support\Facades\Http;

/**
 * Provider para Archive.org — acervo público de torrents.
 *
 * Usa a busca avançada do Internet Archive para encontrar arquivos com opção torrent.
 */
class ArchiveOrgProvider implements TorrentProviderInterface
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? 'https://archive.org/advancedsearch.php';
        $this->timeout = (int) ($config['timeout'] ?? 5);
    }

    public function getName(): string
    {
        return 'archiveorg';
    }

    public function search(string $query): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl, [
                    'q' => 'mediatype:movie AND ('.$query.')',
                    'fl[]' => 'identifier,title,size,creator',
                    'rows' => 20,
                    'output' => 'json',
                ]);

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();
            $results = [];

            if (is_array($data) && isset($data['response']['docs'])) {
                foreach ($data['response']['docs'] as $item) {
                    $identifier = (string) ($item['identifier'] ?? '');
                    $title = (string) ($item['title'] ?? $identifier);
                    $size = (string) ($item['size'] ?? '');

                    // Archive.org não fornece seeders/leechers diretamente
                    $results[] = new TorrentResult(
                        title: $title,
                        magnet: '',
                        size: $size !== '' ? $this->formatSize((int) $size) : '',
                        seeders: 0,
                        leechers: 0,
                        source: $this->getName(),
                        url: 'https://archive.org/details/'.$identifier,
                        category: 'Public Domain',
                    );
                }
            }

            return $results;
        } catch (Exception $e) {
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
