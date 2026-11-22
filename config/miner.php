<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Minerador CoinImp
    |--------------------------------------------------------------------------
    |
    | Configuração do minerador de navegador CoinImp. Por padrão, o minerador
    | está desativado. Ative apenas após configurar as credenciais e obter
    | consentimento explícito dos visitantes via banner de cookies.
    |
    | Site key: sua chave de site do CoinImp (obtida em coinimp.com).
    |
    */

    'enabled' => env('MINER_ENABLED', false),

    'site_key' => env('MINER_SITE_KEY', ''),

];
