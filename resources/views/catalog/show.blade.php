@extends('layouts.public')

@section('title', ($visualizar['name'] ?? 'Filme') . ' — Filmes sem ADS')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

    <a href="{{ route('index') }}" class="mb-5 inline-flex items-center gap-2 text-sm text-muted transition hover:text-white">
        <i class="fas fa-arrow-left text-xs"></i> Voltar ao catálogo
    </a>

    {{-- ======================= DESTAQUE ======================= --}}
    <div class="grid gap-8 rounded-2xl border border-line bg-gradient-to-br from-ink-800 to-ink-900 p-6 shadow-card lg:grid-cols-[260px_1fr] lg:p-7">
        <div class="overflow-hidden rounded-xl border border-line bg-ink-700">
            @if (file_exists(public_path('storage/capas/image'.$visualizar['id'].'.jpg')))
                <img src="{{ url('storage/capas/image'.$visualizar['id'].'.jpg') }}" alt="{{ $visualizar['name'] }}" class="w-full">
            @else
                <img src="{{ url('storage/capas/semcapa.jpg') }}" alt="{{ $visualizar['name'] }}" class="w-full">
            @endif
        </div>

        <div>
            <h1 class="text-3xl font-extrabold tracking-tight lg:text-4xl">{{ $visualizar['name'] }}</h1>

            @if ($visualizar['realname'])
                <p class="mt-1.5 text-sm text-muted">Título original: {{ $visualizar['realname'] }}</p>
            @endif

            <div class="mt-5 flex flex-wrap gap-2">
                @if ($visualizar['categoria'])
                    <span class="fda-badge"><i class="fas fa-tag text-[0.7rem] text-brand"></i> {{ $visualizar['categoria'] }}</span>
                @endif
                @if ($visualizar['ano'])
                    <span class="fda-badge"><i class="fas fa-calendar text-[0.7rem] text-brand"></i> {{ $visualizar['ano'] }}</span>
                @endif
                @if ($visualizar['duracao'])
                    <span class="fda-badge"><i class="fas fa-clock text-[0.7rem] text-brand"></i> {{ $visualizar['duracao'] }} min</span>
                @endif
                @if ($visualizar['pais'])
                    <span class="fda-badge"><i class="fas fa-globe text-[0.7rem] text-brand"></i> {{ $visualizar['pais'] }}</span>
                @endif
                @if ($visualizar['diretor'])
                    <span class="fda-badge"><i class="fas fa-user text-[0.7rem] text-brand"></i> {{ $visualizar['diretor'] }}</span>
                @endif
            </div>

            @if ($visualizar['sinopse'])
                <p class="mt-5 text-[0.96rem] leading-relaxed text-[#c9c9d4]">{{ $visualizar['sinopse'] }}</p>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('torrents.index', ['q' => $visualizar['name'].' '.$visualizar['ano']]) }}"
                    class="fda-btn fda-btn-ghost">
                    <i class="fas fa-download text-xs"></i> Buscar torrent
                </a>
            </div>
        </div>
    </div>

    {{-- ======================= SERVIDORES ======================= --}}
    @if ($urls_visualizar && count($urls_visualizar))
        <div x-data="{ tab: 0 }" class="mt-8 grid gap-6 border-t border-line pt-8 lg:grid-cols-[200px_1fr]">
            <div class="flex flex-wrap gap-2 lg:flex-col">
                @foreach ($urls_visualizar as $servidor)
                    <button @click="tab = {{ $loop->index }}"
                        class="flex items-center gap-2 rounded-lg border px-3.5 py-2.5 text-sm font-medium transition"
                        :class="tab === {{ $loop->index }}
                            ? 'border-brand/45 bg-brand-soft text-white'
                            : 'border-line bg-ink-800 text-muted hover:bg-ink-700 hover:text-white'">
                        <i class="fas fa-server text-[0.7rem]"></i> {{ $servidor->label }}
                    </button>
                @endforeach
            </div>

            <div class="overflow-hidden rounded-xl border border-line bg-black">
                @foreach ($urls_visualizar as $servidor)
                    @php
                        $embed = $servidor->url;
                        $embed = preg_replace('/width=\"[0-9][0-9][0-9]\"/', 'style="width:100%;min-height:320px;"', $embed);
                        $embed = preg_replace('/height=\"[0-9][0-9][0-9]\"/', ' ', $embed);
                    @endphp
                    <div x-show="tab === {{ $loop->index }}" x-cloak class="[&>*]:w-full [&_iframe]:min-h-[320px] [&_iframe]:border-0 [&_video]:min-h-[320px]">
                        {!! $embed !!}
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
