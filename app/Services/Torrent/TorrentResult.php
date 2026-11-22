<?php

namespace App\Services\Torrent;

/**
 * Data object para um resultado de busca de torrent.
 */
class TorrentResult
{
    public function __construct(
        public string $title,
        public string $magnet,
        public string $size = '',
        public int $seeders = 0,
        public int $leechers = 0,
        public string $source = '',
        public string $url = '',
        public ?string $category = null,
    ) {
    }

    /**
     * Formata o tamanho do arquivo para exibição.
     */
    public function sizeFormatted(): string
    {
        if ($this->size === '') {
            return '—';
        }

        return $this->size;
    }
}
