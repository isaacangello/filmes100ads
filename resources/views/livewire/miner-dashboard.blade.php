@props([])

<div class="space-y-6">

    {{-- Status do minerador --}}
    <div class="fda-card-surface p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-soft text-brand">
                    <i class="fas {{ $minerStatus['enabled'] ? 'fa-microchip' : 'fa-power-off' }}"></i>
                </span>
                <div>
                    <h3 class="text-sm font-semibold">Status do minerador</h3>
                    <p class="text-xs text-muted">
                        @if (!$minerStatus['enabled'])
                            Desativado — defina <code class="rounded bg-ink-700 px-1 py-0.5">MINER_ENABLED=true</code> no .env
                        @elseif (!$minerStatus['has_site_key'])
                            Sem chave de site configurada
                        @elseif (is_array($minerStatus['scripts_present']) && !$minerStatus['scripts_present']['all_present'])
                            Scripts ausentes em <code class="rounded bg-ink-700 px-1 py-0.5">public/miner/</code>
                        @elseif (!$hasApiKeys)
                            Minerador ativo — configure <code class="rounded bg-ink-700 px-1 py-0.5">MINER_API_PUBLIC</code> e <code class="rounded bg-ink-700 px-1 py-0.5">MINER_API_PRIVATE</code> para ver estatísticas
                        @elseif ($apiReachable)
                            Conectado à API CoinImp
                        @else
                            Minerador ativo (API indisponível)
                        @endif
                    </p>
                </div>
            </div>
            <div class="text-right">
                @if ($minerStatus['last_checked'])
                    <p class="text-xs text-muted">
                        <i class="fas fa-clock mr-1"></i>
                        {{ \Carbon\Carbon::parse($minerStatus['last_checked'])->format('H:i:s') }}
                    </p>
                @endif
                <button type="button" wire:click="refresh" class="text-xs text-brand hover:underline mt-1">
                    <i class="fas fa-arrows-rotate mr-1"></i>Atualizar
                </button>
            </div>
        </div>
    </div>

    @if ($hasApiKeys && $apiReachable)

        {{-- Cards de estatísticas principais --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Hashes totais --}}
            <div class="fda-card-surface p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-500/20 text-green-400">
                        <i class="fas fa-hashtag"></i>
                    </span>
                    <div>
                        <p class="text-xs text-muted">Hashes totais</p>
                        <p class="text-xl font-bold">{{ number_format($this->getTotalHashes(), 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Recompensa total --}}
            <div class="fda-card-surface p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-soft text-brand">
                        <i class="fas fa-coins"></i>
                    </span>
                    <div>
                        <p class="text-xs text-muted">Recompensa total</p>
                        <p class="text-xl font-bold">{{ $this->formatMint($this->getTotalReward()) }} <span class="text-xs text-muted">MINT</span></p>
                    </div>
                </div>
            </div>

            {{-- Saldo pendente --}}
            <div class="fda-card-surface p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-500/20 text-yellow-400">
                        <i class="fas fa-hourglass-half"></i>
                    </span>
                    <div>
                        <p class="text-xs text-muted">Saldo pendente</p>
                        <p class="text-xl font-bold">{{ $this->formatMint($this->getPendingReward()) }} <span class="text-xs text-muted">MINT</span></p>
                    </div>
                </div>
            </div>

            {{-- Hashrate --}}
            <div class="fda-card-surface p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/20 text-blue-400">
                        <i class="fas fa-tachometer-alt"></i>
                    </span>
                    <div>
                        <p class="text-xs text-muted">Hashrate</p>
                        <p class="text-xl font-bold">{{ $this->formatHashrate($this->getHashrate()) }}</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Info de payout --}}
        @php $payoutRate = $this->getPayoutRate(); @endphp
        @if ($payoutRate > 0)
            <div class="fda-card-surface p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-500/20 text-purple-400">
                        <i class="fas fa-percentage"></i>
                    </span>
                    <div>
                        <p class="text-xs text-muted">Payout por 1M hashes</p>
                        <p class="text-xl font-bold">{{ $this->formatMint($payoutRate) }} <span class="text-xs text-muted">MINT</span></p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Sites configurados --}}
        @php $sites = $this->getSites(); @endphp
        @if (!empty($sites))
            <div class="fda-card-surface p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold">
                        <i class="fas fa-globe mr-2 text-muted"></i>Sites configurados
                    </h3>
                    <span class="text-xs text-muted">{{ count($sites) }} site(s)</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-line text-left text-xs text-muted">
                                <th class="pb-3 pr-4">Nome</th>
                                <th class="pb-3 pr-4">Site Key</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sites as $site)
                                <tr class="border-b border-line last:border-0">
                                    <td class="py-3 pr-4 font-medium">{{ $site['name'] ?? '—' }}</td>
                                    <td class="py-3 font-mono text-xs text-muted">{{ $site['site-key'] ?? $site['site_key'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Histórico de pagamentos --}}
        @php $payments = $this->getPayments(); @endphp
        <div class="fda-card-surface p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold">
                    <i class="fas fa-money-bill-wave mr-2 text-muted"></i>Histórico de pagamentos
                </h3>
                @if (!empty($payments))
                    <span class="text-xs text-muted">{{ count($payments) }} pagamento(s)</span>
                @endif
            </div>

            @if (empty($payments))
                <div class="py-8 text-center">
                    <i class="fas fa-receipt mb-2 block text-xl opacity-50"></i>
                    <p class="text-sm text-muted">Nenhum pagamento registrado ainda.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-line text-left text-xs text-muted">
                                <th class="pb-3 pr-4">Data</th>
                                <th class="pb-3 pr-4">Valor (MINT)</th>
                                <th class="pb-3 pr-4">Método</th>
                                <th class="pb-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr class="border-b border-line last:border-0">
                                    <td class="py-3 pr-4">
                                        @if (isset($payment['date']) && is_numeric($payment['date']))
                                            {{ \Carbon\Carbon::createFromTimestamp($payment['date'])->format('d/m/Y H:i') }}
                                        @elseif (isset($payment['date']))
                                            {{ $payment['date'] }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4 font-medium">
                                        {{ isset($payment['amount']) ? number_format($payment['amount'], 8) : '—' }}
                                    </td>
                                    <td class="py-3 pr-4 text-xs">
                                        {{ $payment['method'] ?? $payment['payment_method'] ?? '—' }}
                                    </td>
                                    <td class="py-3">
                                        @php
                                            $status = $payment['status'] ?? 'unknown';
                                            $badgeClass = match($status) {
                                                'completed', 'paid' => 'bg-green-500/20 text-green-400',
                                                'pending' => 'bg-yellow-500/20 text-yellow-400',
                                                'failed', 'cancelled' => 'bg-red-500/20 text-red-400',
                                                default => 'bg-gray-500/20 text-gray-400',
                                            };
                                            $label = match($status) {
                                                'completed', 'paid' => 'Pago',
                                                'pending' => 'Pendente',
                                                'failed' => 'Falhou',
                                                'cancelled' => 'Cancelado',
                                                default => $status,
                                            };
                                        @endphp
                                        <span class="inline-block rounded-full px-2 py-0.5 text-xs {{ $badgeClass }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Usuários mineradores --}}
        @php $users = $this->getUserList(); @endphp
        @if (!empty($users))
            <div class="fda-card-surface p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold">
                        <i class="fas fa-users mr-2 text-muted"></i>Usuários mineradores
                    </h3>
                    <span class="text-xs text-muted">{{ count($users) }} usuário(s)</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-line text-left text-xs text-muted">
                                <th class="pb-3 pr-4">Usuário</th>
                                <th class="pb-3 pr-4">Hashes</th>
                                <th class="pb-3 pr-4">Saldo (MINT)</th>
                                <th class="pb-3">Carteira</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="border-b border-line last:border-0">
                                    <td class="py-3 pr-4 font-medium">
                                        {{ $user['username'] ?? $user['user'] ?? $user['email'] ?? '—' }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        {{ isset($user['hashes']) ? number_format($user['hashes'], 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        {{ isset($user['balance']) ? number_format($user['balance'], 8) : '—' }}
                                    </td>
                                    <td class="py-3 text-xs font-mono text-muted truncate max-w-[150px]">
                                        {{ $user['wallet'] ?? $user['address'] ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    @elseif ($hasApiKeys && !$apiReachable)
        {{-- Erro de conexão com a API --}}
        <div class="rounded-2xl border border-dashed border-red-500/30 bg-red-500/5 p-8 text-center">
            <i class="fas fa-exclamation-triangle mb-3 block text-2xl text-red-400"></i>
            <p class="text-sm text-red-300">Não foi possível conectar à API do CoinImp.</p>
            @if ($minerStatus['api_error'])
                <p class="mt-2 text-xs text-muted font-mono">{{ $minerStatus['api_error'] }}</p>
            @endif
            <p class="mt-3 text-xs text-muted">Verifique as chaves API e a conexão de internet.</p>
        </div>
    @elseif (!$hasApiKeys)
        {{-- Sem API keys configuradas --}}
        <div class="rounded-2xl border border-dashed border-line-strong bg-ink-850 p-8 text-center">
            <i class="fas fa-key mb-3 block text-2xl text-blue-400"></i>
            <p class="text-sm text-blue-300">Configure as chaves de API para ver estatísticas detalhadas.</p>
            <p class="mt-2 text-xs text-muted">
                Defina <code class="rounded bg-ink-700 px-1 py-0.5">MINER_API_PUBLIC</code> e
                <code class="rounded bg-ink-700 px-1 py-0.5">MINER_API_PRIVATE</code> no .env.
            </p>
            <p class="mt-1 text-xs text-muted">
                Gere as chaves em <code class="rounded bg-ink-700 px-1 py-0.5">https://www.coinimp.com/dashboard</code> (seção API).
            </p>
        </div>
    @endif

</div>
