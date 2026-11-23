<?php

namespace App\Livewire;

use App\Services\MinerStatusService;
use Livewire\Component;

/**
 * Dashboard do minerador CoinImp.
 *
 * Exibe estatísticas completas da conta CoinImp: hashes, recompensas,
 * sites configurados, pagamentos e usuários mineradores.
 */
class MinerDashboard extends Component
{
    public array $minerStatus = [];
    public array $apiData = [];
    public bool $hasApiKeys = false;
    public bool $apiReachable = false;

    public function mount(): void
    {
        $this->loadData();
    }

    public function refresh(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $service = new MinerStatusService();
        $this->minerStatus = $service->getStatus();
        $this->hasApiKeys = $this->minerStatus['has_api_keys'] ?? false;
        $this->apiReachable = $this->minerStatus['api_reachable'] ?? false;
        $this->apiData = $this->minerStatus['api_response'] ?? [];
    }

    /**
     * Extrai os dados de estatísticas da conta.
     */
    public function getAccountStats(): array
    {
        $stats = $this->apiData['account_stats'] ?? [];
        $message = $stats['message'] ?? [];

        return [
            'hashrate' => $message['hashrate'] ?? 0,
            'hashes' => $message['hashes'] ?? 0,
            'reward' => $message['reward'] ?? 0,
            'reward_pending' => $message['reward-pending'] ?? 0,
        ];
    }

    /**
     * Extrai a lista de sites.
     */
    public function getSites(): array
    {
        $sites = $this->apiData['account_sites'] ?? [];
        $message = $sites['message'] ?? [];

        if (is_array($message)) {
            return $message;
        }

        return [];
    }

    /**
     * Extrai o histórico de pagamentos.
     */
    public function getPayments(): array
    {
        $payments = $this->apiData['account_payments'] ?? [];
        $message = $payments['message'] ?? [];

        if (is_array($message)) {
            return $message;
        }

        return [];
    }

    /**
     * Extrai o total de hashes.
     */
    public function getTotalHashes(): int
    {
        // Tenta account/stats primeiro
        $stats = $this->getAccountStats();
        if ($stats['hashes'] > 0) {
            return $stats['hashes'];
        }

        // Fallback para endpoint hashes
        $hashes = $this->apiData['hashes'] ?? [];
        $message = $hashes['message'] ?? 0;

        return (int) $message;
    }

    /**
     * Extrai o total de recompensa.
     */
    public function getTotalReward(): float
    {
        $stats = $this->getAccountStats();
        if ($stats['reward'] > 0) {
            return $stats['reward'];
        }

        $reward = $this->apiData['reward'] ?? [];
        $message = $reward['message'] ?? 0;

        return (float) $message;
    }

    /**
     * Extrai o saldo pendente.
     */
    public function getPendingReward(): float
    {
        $stats = $this->getAccountStats();
        return $stats['reward_pending'] ?? 0;
    }

    /**
     * Extrai o hashrate atual.
     */
    public function getHashrate(): float
    {
        $stats = $this->getAccountStats();
        return $stats['hashrate'] ?? 0;
    }

    /**
     * Extrai a taxa de payout por 1 milhão de hashes.
     *
     * O endpoint payout/1mhash retorna {"message":{"result":"0.54206933"}},
     * diferente dos outros endpoints que retornam message como valor direto.
     */
    public function getPayoutRate(): float
    {
        $payout = $this->apiData['payout_1mhash'] ?? [];
        $message = $payout['message'] ?? [];

        // O endpoint retorna message como array com chave "result"
        if (is_array($message) && isset($message['result'])) {
            return (float) $message['result'];
        }

        // Fallback: se message for um valor escalar
        return (float) $message;
    }

    /**
     * Extrai a lista de usuários mineradores.
     */
    public function getUserList(): array
    {
        $users = $this->apiData['user_list'] ?? [];
        $message = $users['message'] ?? [];

        if (is_array($message)) {
            return $message;
        }

        return [];
    }

    /**
     * Extrai o saldo de usuários.
     */
    public function getUserBalance(): float
    {
        $balance = $this->apiData['user_balance'] ?? [];
        $message = $balance['message'] ?? 0;

        return (float) $message;
    }

    /**
     * Formata hashrate para exibição (H/s, KH/s, MH/s).
     */
    public function formatHashrate(float $hashrate): string
    {
        if ($hashrate >= 1000000) {
            return number_format($hashrate / 1000000, 2) . ' MH/s';
        }
        if ($hashrate >= 1000) {
            return number_format($hashrate / 1000, 2) . ' KH/s';
        }
        return number_format($hashrate, 2) . ' H/s';
    }

    /**
     * Formata valor MINT para exibição.
     */
    public function formatMint(float $value): string
    {
        return number_format($value, 8, '.', '');
    }

    public function render()
    {
        return view('livewire.miner-dashboard');
    }
}
