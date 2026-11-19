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
                @guest
                    <a href="{{ route('register') }}" class="fda-btn fda-btn-ghost">Criar conta grátis</a>
                @else
                    <a href="{{ route('adm') }}" class="fda-btn fda-btn-ghost">
                        <i class="fas fa-user-circle text-xs"></i> Minha área
                    </a>
                @endguest
            </div>

            <div class="mt-11 flex flex-wrap gap-x-10 gap-y-5">
                <div>
                    <strong class="block text-2xl font-extrabold tracking-tight">{{ number_format($total_regs) }}</strong>
                    <span class="text-xs tracking-[0.09em] text-muted uppercase">Filmes</span>
                </div>
                <div>
                    <strong class="block text-2xl font-extrabold tracking-tight">{{ $categorias->count() }}</strong>
                    <span class="text-xs tracking-[0.09em] text-muted uppercase">Categorias</span>
                </div>
                <div>
                    <strong class="block text-2xl font-extrabold tracking-tight">0</strong>
                    <span class="text-xs tracking-[0.09em] text-muted uppercase">Propagandas</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ======================= CATÁLOGO ======================= --}}
    <section id="catalogo" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-baseline justify-between gap-3 border-b border-line pb-4">
            <h2 class="text-xl font-bold tracking-tight">
                @if ($categoria_nome)
                    {{ $categoria_nome->name }}
                @else
                    Catálogo completo
                @endif
            </h2>
            <span class="text-sm text-muted">{{ number_format($total_regs) }} {{ $total_regs == 1 ? 'filme' : 'filmes' }}</span>
        </div>

        {{-- Chips de categorias --}}
        <div class="fda-scroll-x -mx-4 mt-6 flex gap-2 px-4 pb-2 sm:mx-0 sm:flex-wrap sm:px-0">
            <a href="{{ route('index') }}" class="fda-chip {{ $cate_var ? '' : 'fda-chip-active' }}">Todos</a>
            @foreach ($categorias as $categoria)
                <a href="{{ route('index', ['categorias' => $categoria->id]) }}"
                    class="fda-chip {{ ($cate_var == $categoria->id) ? 'fda-chip-active' : '' }}">
                    {{ $categoria->name }}
                </a>
            @endforeach
        </div>

        @if ($posts && count($posts))
            <div class="mt-8 grid grid-cols-2 gap-x-3.5 gap-y-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-7">
                @foreach ($posts as $post)
                    <a href="{{ route('index', ['filmeid' => $post->id]) }}" title="{{ $post->name }}"
                        class="group block transition hover:-translate-y-1">
                        <div class="relative aspect-[2/3] overflow-hidden rounded-xl border border-line bg-ink-700 shadow-card transition group-hover:border-line-strong group-hover:shadow-card-hover">
                            @if ($post->ano)
                                <span class="absolute top-2 right-2 z-10 rounded-md bg-black/70 px-1.5 py-0.5 text-[0.7rem] font-semibold backdrop-blur-sm">
                                    {{ $post->ano }}
                                </span>
                            @endif

                            @if (file_exists(public_path('storage/capas/image'.$post->id.'.jpg')))
                                <img src="{{ url('storage/capas/image'.$post->id.'.jpg') }}" alt="{{ $post->name }}"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <img src="{{ url('storage/capas/semcapa.jpg') }}" alt="{{ $post->name }}" loading="lazy"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @endif

                            <div class="absolute inset-0 z-10 flex items-center justify-center bg-gradient-to-t from-black/70 to-transparent opacity-0 transition group-hover:opacity-100">
                                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand/90 text-white shadow-lg">
                                    <i class="fas fa-play"></i>
                                </span>
                            </div>
                        </div>

                        <div class="px-0.5 pt-2.5">
                            <h3 class="line-clamp-2 text-sm leading-snug font-semibold">{{ $post->name }}</h3>
                            <div class="mt-1 flex items-center gap-1.5 text-xs text-muted">
                                @if ($post->duracao)
                                    <span>{{ $post->duracao }} min</span>
                                @endif
                                @if (! empty($categoriaPorPost[$post->id]))
                                    <span>•</span>
                                    <span class="truncate">{{ $categoriaPorPost[$post->id] }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            @php
                $totalPaginas = (int) ceil($total_regs / $exib_regs);
                $paramsPag = $cate_var ? ['categorias' => $cate_var] : [];
            @endphp

            @if ($totalPaginas > 1)
                <nav class="mt-12 flex justify-center" aria-label="Paginação do catálogo">
                    <ul class="flex flex-wrap items-center gap-1.5">
                        @if ($pag <= 1)
                            <li><span class="block rounded-lg border border-line px-3 py-2 text-sm text-faint">Anterior</span></li>
                        @else
                            <li><a href="{{ route('index', array_merge($paramsPag, ['pag' => $pag - 1])) }}"
                                    class="block rounded-lg border border-line bg-ink-800 px-3 py-2 text-sm text-muted transition hover:bg-ink-700 hover:text-white">Anterior</a></li>
                        @endif

                        @for ($i = max(1, $pag - 3); $i <= min($totalPaginas, $pag + 3); $i++)
                            @if ($i == $pag)
                                <li><span class="block rounded-lg border border-brand bg-brand px-3 py-2 text-sm font-semibold text-white">{{ $i }}</span></li>
                            @else
                                <li><a href="{{ route('index', array_merge($paramsPag, ['pag' => $i])) }}"
                                        class="block rounded-lg border border-line bg-ink-800 px-3 py-2 text-sm text-muted transition hover:bg-ink-700 hover:text-white">{{ $i }}</a></li>
                            @endif
                        @endfor

                        @if ($pag >= $totalPaginas)
                            <li><span class="block rounded-lg border border-line px-3 py-2 text-sm text-faint">Próximo</span></li>
                        @else
                            <li><a href="{{ route('index', array_merge($paramsPag, ['pag' => $pag + 1])) }}"
                                    class="block rounded-lg border border-line bg-ink-800 px-3 py-2 text-sm text-muted transition hover:bg-ink-700 hover:text-white">Próximo</a></li>
                        @endif
                    </ul>
                </nav>
            @endif
        @else
            <div class="mt-8 rounded-xl border border-dashed border-line-strong px-4 py-14 text-center text-muted">
                <i class="fas fa-film mb-3 block text-2xl opacity-70"></i>
                Nenhum filme encontrado nesta categoria.
            </div>
        @endif
    </section>

@endsection
