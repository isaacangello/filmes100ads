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

    <livewire:torrent-search />

</div>
@endsection
