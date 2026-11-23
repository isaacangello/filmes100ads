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
    | Script path: caminho público para o script jiB0.php baixado do CoinImp.
    | Os scripts devem ser baixados em coinimp.com e colocados em public/miner/.
    | O caminho é relativo à raiz pública (sem barra inicial).
    |
    | API Public + API Private: chaves de autenticação para a HTTP API do CoinImp.
    | O serviço MinerStatusService usa ambas as chaves para consultar endpoints como
    | account/stats, hashes, reward, account/sites, account/payments, etc.
    | Gere em: https://www.coinimp.com/dashboard (seção API).
    | Documentação: https://www.coinimp.com/documentation/http-api
    |
    */

    'enabled' => env('MINER_ENABLED', false),

    'site_key' => env('MINER_SITE_KEY', ''),

    'script_path' => env('MINER_SCRIPT_PATH', 'https://www.hostingcloud.racing/RqH3.js'),
    // script_path pode ser uma URL absoluta (CDN) ou um caminho local relativo a public/
    // Exemplos:
    //   CDN: https://www.hostingcloud.racing/RqH3.js
    //   Local: miner/jiB0.php?f=RqH3.js

    'api_public' => env('MINER_API_PUBLIC', ''),

    'api_private' => env('MINER_API_PRIVATE', ''),

    'api_base_url' => 'https://www.coinimp.com',

];
