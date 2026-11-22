<?php

namespace App\Livewire;

use App\Models\Withdrawal;
use App\Services\EarningsService;
use Livewire\Component;

/**
 * Dashboard de rendimentos com dados reais de visualizações.
 */
class EarningsDashboard extends Component
{
    /** Estatísticas do usuário logado. */
    public array $stats = [];

    /** Histórico de saques. */
    public $withdrawals;

    /** Serviço de rendimentos. */
    protected EarningsService $earningsService;

    public function mount(EarningsService $earningsService): void
    {
        $this->earningsService = $earningsService;
        $this->loadData();
    }

    /**
     * Carrega os dados de rendimento do usuário logado.
     */
    public function loadData(): void
    {
        $userId = auth()->id();

        if (!$userId) {
            $this->stats = [];
            $this->withdrawals = collect();
            return;
        }

        $this->stats = $this->earningsService->getStats($userId);
        $this->withdrawals = $this->earningsService->getWithdrawalHistory($userId);
    }

    /**
     * Atualiza os dados manualmente.
     */
    public function refresh(): void
    {
        $this->loadData();
    }

    /**
     * Retorna o label do status do saque.
     */
    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendente',
            'completed' => 'Concluído',
            'rejected' => 'Rejeitado',
            default => ucfirst($status),
        };
    }

    /**
     * Retorna a classe CSS para o badge do status.
     */
    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-yellow-500/20 text-yellow-400',
            'completed' => 'bg-green-500/20 text-green-400',
            'rejected' => 'bg-red-500/20 text-red-400',
            default => 'bg-ink-600 text-muted',
        };
    }

    public function render()
    {
        return view('livewire.earnings-dashboard');
    }
}
