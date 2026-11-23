<?php

namespace App\Services;

/**
 * Serviço para verificar o status do minerador CoinImp.
 *
 * Fornece informações sobre a configuração atual do minerador,
 * presença dos scripts locais e dados da HTTP API oficial do CoinImp.
 *
 * A API HTTP usa autenticação via headers X-API-ID (public) e X-API-KEY (private).
 * Documentação: https://www.coinimp.com/documentation/http-api
 */
class MinerStatusService
{
    /**
     * Retorna o status atual do minerador.
     *
     * @return array com informações do status
     */
    public function getStatus(): array
    {
        $enabled = config('miner.enabled', false);
        $siteKey = config('miner.site_key', '');
        $scriptPath = config('miner.script_path', 'miner/jiB0.php?f=RqH3.js');
        $apiPublic = config('miner.api_public', '');
        $apiPrivate = config('miner.api_private', '');

        // Verifica se os scripts estão presentes no diretório público
        $scriptsPresent = $this->checkScriptsPresent($scriptPath);

        $status = [
            'enabled' => $enabled,
            'has_site_key' => !empty($siteKey),
            'has_api_keys' => !empty($apiPublic) && !empty($apiPrivate),
            'scripts_present' => $scriptsPresent,
            'script_path' => $scriptPath,
            'api_reachable' => false,
            'api_response' => null,
            'api_error' => null,
            'last_checked' => null,
        ];

        // Se o minerador está desativado, não precisa verificar a API
        if (!$enabled || empty($siteKey)) {
            $status['last_checked'] = now()->toDateTimeString();
            return $status;
        }

        // Tenta verificar a API oficial do CoinImp
        $status = $this->checkApiStatus($status, $apiPublic, $apiPrivate);

        return $status;
    }

    /**
     * Verifica se os scripts do minerador estão presentes ou se está usando CDN.
     *
     * @param string $scriptPath O caminho configurado do script (URL ou caminho local)
     * @return array com detalhes dos scripts
     */
    protected function checkScriptsPresent(string $scriptPath): array
    {
        // Se o script_path é uma URL absoluta (CDN), não precisa verificar arquivos locais
        if (strpos($scriptPath, 'http://') === 0 || strpos($scriptPath, 'https://') === 0) {
            return [
                'uses_cdn' => true,
                'cdn_url' => $scriptPath,
                'jiB0_exists' => true,
                'RqH3_exists' => true,
                'all_present' => true,
                'missing' => [],
            ];
        }

        // Script local — verifica arquivos em public/
        $basePath = public_path();
        $scriptWithoutQuery = explode('?', $scriptPath)[0];
        $scriptDir = dirname($scriptWithoutQuery);
        $scriptFile = basename($scriptWithoutQuery);

        $jiB0Path = $basePath . '/' . $scriptDir . '/' . $scriptFile;
        $rqh3Path = $basePath . '/' . $scriptDir . '/RqH3.js';

        $jiB0Exists = file_exists($jiB0Path);
        $rqh3Exists = file_exists($rqh3Path);

        return [
            'uses_cdn' => false,
            'cdn_url' => null,
            'jiB0_exists' => $jiB0Exists,
            'RqH3_exists' => $rqh3Exists,
            'all_present' => $jiB0Exists && $rqh3Exists,
            'missing' => $jiB0Exists ? ($rqh3Exists ? [] : ['RqH3.js']) : ['jiB0.php', 'RqH3.js'],
        ];
    }

    /**
     * Tenta verificar a API HTTP oficial do CoinImp.
     *
     * Usa os endpoints /api/v2/ com autenticação via headers X-API-ID e X-API-KEY.
     * Endpoints consultados: account/stats, account/sites, account/payments,
     * hashes, reward, user/balance, user/list, payout/1mhash.
     *
     * Rate limit: 3 chamadas por minuto (burst de 10).
     */
    protected function checkApiStatus(array $status, string $apiPublic = '', string $apiPrivate = ''): array
    {
        $baseUrl = config('miner.api_base_url', 'https://www.coinimp.com');

        try {
            // Se não tem API keys, tenta apenas o endpoint público
            if (empty($apiPublic) || empty($apiPrivate)) {
                $status = $this->checkPublicEndpoint($status);
                return $status;
            }

            // Usa a API oficial com autenticação
            $apiData = [
                'account_stats' => null,
                'account_sites' => null,
                'account_payments' => null,
                'hashes' => null,
                'reward' => null,
                'user_balance' => null,
                'user_list' => null,
                'payout_1mhash' => null,
            ];

            $auth = ['public' => $apiPublic, 'private' => $apiPrivate];

            // account/stats - estatísticas gerais da conta (hashrate, hashes, reward, reward-pending)
            $accountStats = $this->callApi($baseUrl . '/api/v2/account/stats', $auth);
            if ($accountStats !== null) {
                $apiData['account_stats'] = $accountStats;
                $status['api_reachable'] = true;
            }

            // account/sites - lista de sites
            $accountSites = $this->callApi($baseUrl . '/api/v2/account/sites', $auth);
            if ($accountSites !== null) {
                $apiData['account_sites'] = $accountSites;
                $status['api_reachable'] = true;
            }

            // account/payments - histórico de pagamentos
            $accountPayments = $this->callApi($baseUrl . '/api/v2/account/payments', $auth);
            if ($accountPayments !== null) {
                $apiData['account_payments'] = $accountPayments;
                $status['api_reachable'] = true;
            }

            // hashes - total de hashes
            $hashes = $this->callApi($baseUrl . '/api/v2/hashes', $auth);
            if ($hashes !== null) {
                $apiData['hashes'] = $hashes;
                $status['api_reachable'] = true;
            }

            // reward - recompensa total
            $reward = $this->callApi($baseUrl . '/api/v2/reward', $auth);
            if ($reward !== null) {
                $apiData['reward'] = $reward;
                $status['api_reachable'] = true;
            }

            // user/balance - saldo de usuários
            $userBalance = $this->callApi($baseUrl . '/api/v2/user/balance', $auth);
            if ($userBalance !== null) {
                $apiData['user_balance'] = $userBalance;
                $status['api_reachable'] = true;
            }

            // user/list - lista de usuários mineradores
            $userList = $this->callApi($baseUrl . '/api/v2/user/list', $auth);
            if ($userList !== null) {
                $apiData['user_list'] = $userList;
                $status['api_reachable'] = true;
            }

            // payout/1mhash - taxa de pagamento por 1 milhão de hashes
            $payout = $this->callApi($baseUrl . '/api/v2/payout/1mhash', $auth);
            if ($payout !== null) {
                $apiData['payout_1mhash'] = $payout;
                $status['api_reachable'] = true;
            }

            $status['api_response'] = $apiData;

        } catch (\Exception $e) {
            $status['api_reachable'] = false;
            $status['api_error'] = $e->getMessage();
        }

        $status['last_checked'] = now()->toDateTimeString();

        return $status;
    }

    /**
     * Tenta o endpoint público (fallback quando não há API keys).
     */
    protected function checkPublicEndpoint(array $status): array
    {
        $siteKey = config('miner.site_key', '');

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'ignore_errors' => true,
                ],
            ]);

            $url = 'https://coinimp.com/miner/' . $siteKey . '/stats';
            $response = @file_get_contents($url, false, $context);

            if ($response !== false) {
                $data = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $status['api_reachable'] = true;
                    $status['api_response'] = $data;
                }
            }

        } catch (\Exception $e) {
            $status['api_reachable'] = false;
            $status['api_error'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * Faz uma chamada à API HTTP do CoinImp com autenticação.
     *
     * A API usa headers X-API-ID (public) e X-API-KEY (private) para autenticação.
     * Rate limit: 3 chamadas por minuto (burst de 10).
     *
     * @param string $url URL do endpoint
     * @param array $auth ['public' => string, 'private' => string]
     * @return array|null Dados JSON decodificados ou null em caso de erro
     */
    protected function callApi(string $url, array $auth): ?array
    {
        try {
            // A API autentica via headers, não parâmetros GET
            $headers = [
                'X-API-ID:' . $auth['public'],
                'X-API-KEY:' . $auth['private'],
            ];

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                    'ignore_errors' => true,
                    'header' => implode("\r\n", $headers),
                ],
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            // A API retorna {"status": "success", "message": ...} ou {"status": "failure", "message": ...}
            if (isset($data['status']) && $data['status'] === 'failure') {
                return null;
            }

            return $data;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Retorna o label do status para exibição.
     */
    public function getStatusLabel(array $minerStatus): string
    {
        if (!$minerStatus['enabled']) {
            return 'Desativado';
        }

        if (!$minerStatus['has_site_key']) {
            return 'Sem chave configurada';
        }

        if (is_array($minerStatus['scripts_present']) && !$minerStatus['scripts_present']['all_present']) {
            return 'Scripts ausentes';
        }

        if (!$minerStatus['has_api_keys']) {
            return 'Ativo (sem API keys)';
        }

        if ($minerStatus['api_reachable']) {
            return 'Ativo e conectado';
        }

        return 'Ativo (API indisponível)';
    }

    /**
     * Retorna a classe CSS para o badge do status.
     */
    public function getStatusBadgeClass(array $minerStatus): string
    {
        if (!$minerStatus['enabled']) {
            return 'bg-gray-500/20 text-gray-400';
        }

        if (!$minerStatus['has_site_key']) {
            return 'bg-yellow-500/20 text-yellow-400';
        }

        if (is_array($minerStatus['scripts_present']) && !$minerStatus['scripts_present']['all_present']) {
            return 'bg-red-500/20 text-red-400';
        }

        if (!$minerStatus['has_api_keys']) {
            return 'bg-blue-500/20 text-blue-400';
        }

        if ($minerStatus['api_reachable']) {
            return 'bg-green-500/20 text-green-400';
        }

        return 'bg-orange-500/20 text-orange-400';
    }

    /**
     * Retorna o ícone para o status.
     */
    public function getStatusIcon(array $minerStatus): string
    {
        if (!$minerStatus['enabled']) {
            return 'fa-power-off';
        }

        if (!$minerStatus['has_site_key']) {
            return 'fa-key';
        }

        if (is_array($minerStatus['scripts_present']) && !$minerStatus['scripts_present']['all_present']) {
            return 'fa-exclamation-triangle';
        }

        if (!$minerStatus['has_api_keys']) {
            return 'fa-plug';
        }

        if ($minerStatus['api_reachable']) {
            return 'fa-microchip';
        }

        return 'fa-microchip';
    }
}
