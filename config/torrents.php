<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Providers de Torrent
    |--------------------------------------------------------------------------
    |
    | Cada provider pode ser ativado ou desativado individualmente.
    | Adicione novos providers aqui sem modificar o código.
    |
    */

    'providers' => [

        'bitsearch' => [
            'enabled' => true,
            'base_url' => 'https://bitsearch.to',
            'timeout' => 10,
            'headers' => [],
        ],

        'solidtorrents' => [
            'enabled' => false,
            'base_url' => 'https://solidtorrents.to/api',
            'timeout' => 5,
            'headers' => [],
        ],

        'nyaa' => [
            'enabled' => false,
            'base_url' => 'https://nyaa.si/api?',
            'timeout' => 5,
            'headers' => [],
        ],

        'archiveorg' => [
            'enabled' => true,
            'base_url' => 'https://archive.org/advancedsearch.php',
            'timeout' => 5,
            'headers' => [],
        ],

        'baixetorrent' => [
            'enabled' => true,
            'base_url' => 'https://www.baixetorrent.org',
            'timeout' => 10,
            'headers' => [],
        ],

        /*
        | Exemplo de provider customizado:
        |
        | 'meuprovider' => [
        |     'enabled' => true,
        |     'base_url' => 'https://api.meusite.com/torrents',
        |     'timeout' => 10,
        |     'headers' => [
        |         'Authorization' => 'Bearer ' . env('MEU_TOKEN'),
        |     ],
        | ],
        */
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Tempo em segundos para cachear os resultados de busca.
    |
    */

    '_cache_ttl' => 300,

    /*
    |--------------------------------------------------------------------------
    | Filtros padrão
    |--------------------------------------------------------------------------
    |
    | Categorias e filtros aplicados por padrão em todas as buscas.
    |
    */

    'filters' => [
        'categories' => ['movies', 'film', 'video'],
        'min_seeders' => 0,
    ],

];
