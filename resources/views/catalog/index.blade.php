@extends('layouts.public')

@section('title', 'Catálogo de filmes sem chatisse')

@section('alerta')
    @if ($message = Session::get('success'))
        <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-emerald-500/35 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ $message }}
            </div>
        </div>
    @endif
@endsection

@section('content')

    {{-- ======================= HERO COMPACTO ======================= --}}
    <section class="relative overflow-hidden border-b border-line bg-gradient-to-b from-ink-900 to-ink-950">
        <div class="pointer-events-none absolute -top-32 left-0 h-64 w-[600px] rounded-full bg-brand/15 blur-[100px]"></div>
        <div class="pointer-events-none absolute -top-20 right-0 h-48 w-[500px] rounded-full bg-indigo-600/10 blur-[80px]"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            {{-- FILTROS DE CATEGORIA E ANO (acima do slogan) --}}
            <div class="fda-scroll-x -mx-4 flex gap-2 px-4 pb-3 sm:mx-0 sm:flex-wrap sm:px-0">
                <span class="text-xs text-muted self-center mr-1"><i class="fas fa-filter text-[0.6rem]"></i> Filtros:</span>
                <a href="{{ route('index') }}" class="fda-chip {{ request('categorias') === null && request('ano') === null ? 'fda-chip-active' : '' }}">
                    Todos
                </a>
                @foreach($categoriasMenu ?? [] as $cat)
                    <a href="{{ route('index', ['categorias' => $cat->id]) }}"
                        class="fda-chip {{ (string) request('categorias') === (string) $cat->id ? 'fda-chip-active' : '' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>

            <div class="fda-scroll-x -mx-4 mt-2 flex gap-2 px-4 pb-3 sm:mx-0 sm:flex-wrap sm:px-0">
                <span class="text-xs text-muted self-center mr-1"><i class="fas fa-calendar text-[0.6rem]"></i> Ano:</span>
                <a href="{{ route('index') }}" class="fda-chip {{ request('ano') === null ? 'fda-chip-active' : '' }}">
                    Todos
                </a>
                @php $anoCorrente = (int) date('Y'); @endphp
                @foreach(range($anoCorrente, $anoCorrente - 2) as $anoItem)
                    <a href="{{ route('index', ['ano' => $anoItem]) }}"
                        class="fda-chip {{ (string) request('ano') === (string) $anoItem ? 'fda-chip-active' : '' }}">
                        {{ $anoItem }}
                    </a>
                @endforeach
            </div>

            {{-- SLOGAN EM UMA LINHA + BUSCA --}}
            <div class="mt-4 flex flex-col items-center gap-4 text-center sm:flex-row sm:text-left">
                <div class="flex-1">
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl lg:text-4xl">
                        Filmes <span class="text-brand">sem chatisse</span>, sem propagandas
                    </h1>
                </div>

                {{-- BUSCA --}}
                <form action="{{ route('index') }}" method="GET" class="w-full sm:w-auto sm:min-w-[240px]">
                    <div class="relative">
                        <i class="fas fa-magnifying-glass pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-xs text-faint"></i>
                        <input type="search" name="busca" value="{{ request('busca', '') }}" placeholder="Buscar por título…"
                            class="fda-input pl-9 w-full">
                    </div>
                </form>
            </div>

            {{-- LINHA DE CONTADORES + EXPLORAR --}}
            <div class="mt-4 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-center sm:justify-start">
                <a href="#catalogo" class="fda-btn fda-btn-primary">
                    <i class="fas fa-film text-xs"></i> Explorar catálogo
                </a>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-muted">{{ number_format($totalFilmes) }} <strong class="text-white">filmes</strong></span>
                    <span class="text-muted">•</span>
                    <span class="text-muted">{{ number_format($totalCategorias) }} <strong class="text-white">categorias</strong></span>
                    <span class="text-muted">•</span>
                    <span class="text-muted"><strong class="text-white">0</strong> propagandas</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ======================= CATÁLOGO (Livewire) ======================= --}}
    <section id="catalogo" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <livewire:movie-catalog />
    </section>

@endsection
