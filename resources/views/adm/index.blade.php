@extends('layouts.admin')

@section('page-title', 'Painel')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">

    {{-- ======================= INDICADORES ======================= --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $cards = [
                ['label' => 'Filmes no catálogo', 'value' => $stats['totalFilmes'], 'icon' => 'fa-film', 'tone' => 'brand'],
                ['label' => 'Meus cadastros', 'value' => $stats['meusFilmes'], 'icon' => 'fa-user-circle', 'tone' => 'indigo'],
                ['label' => 'Links de servidores', 'value' => $stats['totalLinks'], 'icon' => 'fa-link', 'tone' => 'emerald'],
                ['label' => 'Categorias', 'value' => $stats['totalCategorias'], 'icon' => 'fa-layer-group', 'tone' => 'amber'],
            ];

            $tones = [
                'brand' => 'bg-brand-soft text-brand',
                'indigo' => 'bg-indigo-500/15 text-indigo-300',
                'emerald' => 'bg-emerald-500/15 text-emerald-400',
                'amber' => 'bg-amber-500/15 text-amber-300',
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="rounded-2xl border border-line bg-gradient-to-br from-ink-800 to-ink-700 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs tracking-[0.09em] text-muted uppercase">{{ $card['label'] }}</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight">{{ number_format($card['value']) }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $tones[$card['tone']] }}">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- ======================= ÚLTIMOS FILMES ======================= --}}
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-2xl border border-line bg-ink-800 shadow-card">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h2 class="font-semibold">Últimos filmes cadastrados</h2>
                    <a href="{{ route('meuscadastros.index') }}"
                        class="text-xs font-semibold text-muted transition hover:text-white">Ver todos</a>
                </div>

                @if ($ultimosFilmes->count())
                    <div class="overflow-x-auto">
                        <table class="fda-table">
                            <thead>
                                <tr>
                                    <th class="w-14">#</th>
                                    <th>Filme</th>
                                    <th class="hidden sm:table-cell">Autor</th>
                                    <th class="w-24 text-right">Ano</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ultimosFilmes as $filme)
                                    <tr>
                                        <td class="text-muted">{{ $filme->id }}</td>
                                        <td>
                                            <a href="{{ route('index', ['filmeid' => $filme->id]) }}"
                                                class="font-medium transition hover:text-brand-hover">{{ $filme->name }}</a>
                                        </td>
                                        <td class="hidden text-muted sm:table-cell">{{ $filme->autor ?? '—' }}</td>
                                        <td class="text-right text-muted">{{ $filme->ano ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-5 py-12 text-center text-sm text-muted">Nenhum filme cadastrado ainda.</div>
                @endif
            </div>
        </div>

        {{-- ======================= TOP CATEGORIAS + ATALHOS ======================= --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-line bg-ink-800 p-5 shadow-card">
                <h2 class="font-semibold">Categorias mais usadas</h2>

                @if ($filmesPorCategoria->count())
                    <ul class="mt-4 space-y-3">
                        @php $max = max($filmesPorCategoria->max('total'), 1); @endphp
                        @foreach ($filmesPorCategoria as $categoria)
                            <li>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted">{{ $categoria->name }}</span>
                                    <span class="font-semibold">{{ $categoria->total }}</span>
                                </div>
                                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-ink-700">
                                    <div class="h-full rounded-full bg-gradient-to-r from-brand to-orange-400"
                                        style="width: {{ max(4, round($categoria->total / $max * 100)) }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-sm text-muted">Sem dados ainda.</p>
                @endif
            </div>

            <div class="rounded-2xl border border-line bg-ink-800 p-5 shadow-card">
                <h2 class="font-semibold">Ações rápidas</h2>
                <div class="mt-4 space-y-2">
                    <a href="{{ route('cadastro.index') }}"
                        class="flex items-center gap-3 rounded-xl border border-line bg-ink-850 px-4 py-3 text-sm transition hover:border-brand/40 hover:bg-ink-700">
                        <i class="fas fa-plus text-brand"></i> Cadastrar novo filme
                    </a>
                    <a href="{{ route('meuscadastros.index') }}"
                        class="flex items-center gap-3 rounded-xl border border-line bg-ink-850 px-4 py-3 text-sm transition hover:border-brand/40 hover:bg-ink-700">
                        <i class="fas fa-list text-brand"></i> Meus cadastros
                    </a>
                    <a href="{{ route('torrents.index') }}"
                        class="flex items-center gap-3 rounded-xl border border-line bg-ink-850 px-4 py-3 text-sm transition hover:border-brand/40 hover:bg-ink-700">
                        <i class="fas fa-download text-brand"></i> Buscar torrents
                    </a>
                    <a href="{{ route('index') }}"
                        class="flex items-center gap-3 rounded-xl border border-line bg-ink-850 px-4 py-3 text-sm transition hover:border-brand/40 hover:bg-ink-700">
                        <i class="fas fa-home text-brand"></i> Ver o site
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
