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

    {{-- ======================= HERO ======================= --}}
    <section class="relative overflow-hidden border-b border-line bg-gradient-to-b from-ink-900 to-ink-950">
        <div class="pointer-events-none absolute -top-32 left-0 h-96 w-[900px] rounded-full bg-brand/20 blur-[120px]"></div>
        <div class="pointer-events-none absolute -top-24 right-0 h-80 w-[700px] rounded-full bg-indigo-600/15 blur-[120px]"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <h1 class="max-w-3xl text-4xl leading-[1.06] font-extrabold tracking-tight sm:text-5xl">
                Assista filmes <span class="text-brand">sem chatisse</span>,<br>
                sem propagandas.
            </h1>
            <p class="mt-5 max-w-xl text-base leading-relaxed text-muted">
                Um catálogo de filmes com sinopse, ficha técnica e links de servidores para assistir
                direto — sem commercials no meio do filme e sem rastreamento.
            </p>

            <div class="mt-7 flex flex-wrap gap-3">
                <a href="#catalogo" class="fda-btn fda-btn-primary">
                    <i class="fas fa-film text-xs"></i> Explorar catálogo
                </a>
                @auth
                    <a href="{{ route('adm') }}" class="fda-btn fda-btn-ghost">
                        <i class="fas fa-user-circle text-xs"></i> Minha área
                    </a>
                @endauth
            </div>

            <div class="mt-11 flex flex-wrap gap-x-10 gap-y-5">
                <div>
                    <strong class="block text-2xl font-extrabold tracking-tight">{{ number_format($totalFilmes) }}</strong>
                    <span class="text-xs tracking-[0.09em] text-muted uppercase">Filmes</span>
                </div>
                <div>
                    <strong class="block text-2xl font-extrabold tracking-tight">{{ number_format($totalCategorias) }}</strong>
                    <span class="text-xs tracking-[0.09em] text-muted uppercase">Categorias</span>
                </div>
                <div>
                    <strong class="block text-2xl font-extrabold tracking-tight">0</strong>
                    <span class="text-xs tracking-[0.09em] text-muted uppercase">Propagandas</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ======================= CATÁLOGO (Livewire) ======================= --}}
    <section id="catalogo" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <livewire:movie-catalog />
    </section>

@endsection
