@props([])

<div class="space-y-8">

    {{-- Cards de estatísticas --}}
    @if (!empty($stats))
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Ganho total --}}
            <div class="fda-card-surface p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-soft text-brand">
                        <i class="fas fa-coins"></i>
                    </span>
                    <div>
                        <p class="text-xs text-muted">Ganho total</p>
                        <p class="text-xl font-bold">{{ number_format($stats['earnings_total'], 2) }} <span class="text-xs text-muted">MINT</span></p>
                    </div>
                </div>
            </div>

            {{-- Visualizações totais --}}
            <div class="fda-card-surface p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/20 text-blue-400">
                        <i class="fas fa-eye"></i>
                    </span>
                    <div>
                        <p class="text-xs text-muted">Visualizações</p>
                        <p class="text-xl font-bold">{{ number_format($stats['views_total']) }}</p>
                    </div>
                </div>
            </div>

            {{-- Ganho hoje --}}
            <div class="fda-card-surface p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-500/20 text-green-400">
                        <i class="fas fa-calendar-day"></i>
                    </span>
                    <div>
                        <p class="text-xs text-muted">Ganho hoje</p>
                        <p class="text-xl font-bold">{{ number_format($stats['earnings_today'], 2) }} <span class="text-xs text-muted">MINT</span></p>
                    </div>
                </div>
            </div>

            {{-- Visualizações hoje --}}
            <div class="fda-card-surface p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-500/20 text-purple-400">
                        <i class="fas fa-chart-line"></i>
                    </span>
                    <div>
                        <p class="text-xs text-muted">Views hoje</p>
                        <p class="text-xl font-bold">{{ number_format($stats['views_today']) }}</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Resumo mensal --}}
        <div class="fda-card-surface p-5">
            <h3 class="text-sm font-semibold mb-3">Resumo do mês</h3>
            <div class="grid gap-3 sm:grid-cols-3">
                <div>
                    <p class="text-xs text-muted">Visualizações no mês</p>
                    <p class="text-lg font-semibold">{{ number_format($stats['views_this_month']) }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted">Ganho no mês</p>
                    <p class="text-lg font-semibold">{{ number_format($stats['earnings_this_month'], 2) }} MINT</p>
                </div>
                <div>
                    <p class="text-xs text-muted">Valor por visualização</p>
                    <p class="text-lg font-semibold">0.01 MINT</p>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-line-strong bg-ink-850 p-8 text-center">
            <i class="fas fa-chart-pie mb-3 block text-2xl opacity-70"></i>
            <p class="text-sm text-muted">Faça login para ver seus rendimentos.</p>
        </div>
    @endif

    {{-- Histórico de saques --}}
    <div class="fda-card-surface p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold">Histórico de saques</h3>
            @if (!empty($stats))
                <span class="text-xs text-muted">
                    {{ $stats['withdrawals_count'] }} saque(s) · {{ $stats['withdrawals_pending'] }} pendente(s)
                </span>
            @endif
        </div>

        @if ($withdrawals->isEmpty())
            <div class="py-8 text-center">
                <i class="fas fa-wallet mb-2 block text-xl opacity-50"></i>
                <p class="text-sm text-muted">Nenhum saque registrado ainda.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line text-left text-xs text-muted">
                            <th class="pb-3 pr-4">Data</th>
                            <th class="pb-3 pr-4">Valor (MINT)</th>
                            <th class="pb-3 pr-4">Valor (BTC)</th>
                            <th class="pb-3 pr-4">Status</th>
                            <th class="pb-3">Carteira</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($withdrawals as $withdrawal)
                            <tr class="border-b border-line last:border-0">
                                <td class="py-3 pr-4">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 pr-4">{{ number_format($withdrawal->amount_mint, 2) }}</td>
                                <td class="py-3 pr-4">{{ number_format($withdrawal->amount_btc, 8) }}</td>
                                <td class="py-3 pr-4">
                                    <span class="inline-block rounded-full px-2 py-0.5 text-xs {{ $this->getStatusBadgeClass($withdrawal->status) }}">
                                        {{ $this->getStatusLabel($withdrawal->status) }}
                                    </span>
                                </td>
                                <td class="py-3 text-xs text-muted font-mono truncate max-w-[120px]">
                                    {{ $withdrawal->btc_address }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Botão de atualização --}}
    @if (!empty($stats))
        <div class="text-center">
            <button type="button" wire:click="refresh" class="fda-btn fda-btn-secondary">
                <i class="fas fa-arrows-rotate text-xs"></i> Atualizar dados
            </button>
        </div>
    @endif

</div>
