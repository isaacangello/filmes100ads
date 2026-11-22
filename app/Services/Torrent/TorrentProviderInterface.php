<?php

namespace App\Services\Torrent;

/**
 * Interface que todos os providers de torrent devem implementar.
 */
interface TorrentProviderInterface
{
    /**
     * Busca torrents pelo termo informado.
     *
     * @return TorrentResult[]
     */
    public function search(string $query): array;

    /**
     * Retorna o identificador do provider (ex: 'solidtorrents').
     */
    public function getName(): string;
}
