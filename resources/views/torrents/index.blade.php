@extends('layouts.public')

@section('title', 'Busca de torrents — Filmes sem ADS')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">

    <div class="mb-8">
        <span class="fda-badge">
            <i class="fas fa-download text-[0.7rem] text-brand"></i> SolidTorrents · Nyaa · Archive.org
        </span>
        <h1 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">Buscar torrents</h1>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-muted">
            Pesquise por um título e encontre arquivos disponíveis nas APIs públicas de torrents.
            Os resultados são agregados em um único lugar, sem rastreamento.
        </p>
    </div>

    <form method="GET" action="{{ route('torrents.index') }}" class="flex flex-wrap gap-3">
        <div class="min-w-0 flex-1">
            <label for="q" class="sr-only">Termo de busca</label>
            <input id="q" type="search" name="q" value="{{ $termo }}"
                placeholder="Ex.: Matrix 1999 dublado" class="fda-input" autofocus>
        </div>
        <button type="submit" class="fda-btn fda-btn-primary">
            <i class="fas fa-magnifying-glass text-xs"></i> Buscar
        </button>
    </form>

    @if ($termo !== '')
        <div class="mt-8 rounded-2xl border border-dashed border-line-strong bg-ink-850 p-8 text-center">
            <i class="fas fa-cloud-arrow-down mb-3 block text-2xl opacity-70"></i>
            <p class="text-sm text-muted">
                A busca integrada está sendo habilitada na próxima etapa.
            </p>
            <p class="mt-2 text-xs text-faint">Termo pesquisado: <span class="text-chalk">{{ $termo }}</span></p>
        </div>
    @else
        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['SolidTorrents', 'Filmes pt-BR priorizados por seeders.', 'fa-layer-group'],
                ['Nyaa', 'Catálogo de quadrado, anime e software.', 'fa-ghost'],

                ['Archive.org', 'Acervo aberto com arquivos públicos.', 'fa-box-archive'],
            ] as $fonte)
                <div class="rounded-2xl border border-line bg-ink-800 p-5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-soft text-xs text-brand">
                        <i class="fas {{ $fonte[2] }}"></i>
                    </span>
                    <h2 class="mt-3 text-sm font-semibold">{{ $fonte[0] }}</h2>
                    <p class="mt-1 text-xs text-muted">{{ $fonte[1] }}</p>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
