@extends('layouts.admin')

@section('page-title', 'Rendimentos')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">

    <div class="rounded-2xl border border-line bg-gradient-to-br from-ink-800 to-ink-700 p-6 shadow-card">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold">Rendimentos</h2>
                <p class="mt-1.5 max-w-xl text-sm text-muted">
                    Acompanhe os ganhos gerados pelas suas publicações — por visualizações e por downloads
                    de torrents. Os valores são convertidos de MINTME para BTC antes do saque.
                </p>
            </div>

            <span class="fda-badge">
                <i class="fas fa-circle-info text-[0.7rem] text-brand"></i> Em preparação
            </span>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $cards = [
                ['label' => 'Ganho total', 'value' => '0 MINTME', 'icon' => 'fa-coins', 'tone' => 'bg-amber-500/15 text-amber-300'],
                ['label' => 'Visualizações', 'value' => '0', 'icon' => 'fa-eye', 'tone' => 'bg-indigo-500/15 text-indigo-300'],
                ['label' => 'Downloads', 'value' => '0', 'icon' => 'fa-download', 'tone' => 'bg-emerald-500/15 text-emerald-400'],
                ['label' => 'Conversão BTC', 'value' => '—', 'icon' => 'fa-bitcoin-sign', 'tone' => 'bg-brand-soft text-brand'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="rounded-2xl border border-line bg-ink-800 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs tracking-[0.09em] text-muted uppercase">{{ $card['label'] }}</p>
                        <p class="mt-2 text-2xl font-extrabold tracking-tight">{{ $card['value'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $card['tone'] }}">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-line bg-ink-800 shadow-card">
        <div class="border-b border-line px-5 py-4">
            <h2 class="font-semibold">Histórico de saques</h2>
        </div>

        <div class="px-5 py-14 text-center text-muted">
            <i class="fas fa-receipt mb-3 block text-2xl opacity-70"></i>
            Nenhum saque registrado até o momento.
        </div>
    </div>

    <div class="rounded-2xl border border-dashed border-line-strong bg-ink-850 p-6 text-sm text-muted">
        <p class="flex items-start gap-2.5">
            <i class="fas fa-circle-info mt-0.5 text-brand"></i>
            <span>
                Os pagamentos são feitos em <strong class="text-chalk">MINTME</strong> via CoinIMP.
                A conversão para BTC é feita manualmente e o saque vai para o endereço
                <code class="rounded bg-ink-700 px-1.5 py-0.5 text-xs text-chalk">bc1q4wyln7kwu5p3fllsx5gec698qkhuphnh5vscdt</code>.
            </span>
        </p>
    </div>

</div>
@endsection
