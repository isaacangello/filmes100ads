<div wire:key="movie-catalog">

    {{-- ======================= CABEÇALHO + FILTROS ======================= --}}
    <div class="flex flex-wrap items-baseline justify-between gap-3 border-b border-line pb-4">
        <h2 class="text-xl font-bold tracking-tight">
            @if ($categoria)
                {{ $categoria->name }}
            @elseif (trim((string) $busca) !== '')
                Resultados para "{{ $busca }}"
            @else
                Catálogo completo
            @endif
        </h2>

        <span class="text-sm text-muted" wire:loading.delay>
            <span wire:loading.remove>
                {{ number_format($total) }} {{ $total == 1 ? 'filme' : 'filmes' }}
            </span>
            <span wire:loading>
                <i class="fas fa-circle-notch fa-spin text-xs"></i> Carregando…
            </span>
        </span>
    </div>

    {{-- ======================= BUSCA ======================= --}}
    <div class="mt-6 flex flex-wrap items-center gap-3">
        <div class="relative min-w-0 flex-1 sm:max-w-sm">
            <i class="fas fa-magnifying-glass pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-xs text-faint"></i>
            <input type="search" wire:model.live.debounce.400ms="busca" placeholder="Buscar por título…"
                class="fda-input pl-9" aria-label="Buscar filmes por título">
        </div>

        @if (trim((string) $busca) !== '' || $categoriaId !== '')
            <button type="button" wire:click="limparFiltros" class="fda-btn fda-btn-ghost">
                <i class="fas fa-xmark text-xs"></i> Limpar filtros
            </button>
        @endif
    </div>

    {{-- ======================= CHIPS DE CATEGORIAS ======================= --}}
    <div class="fda-scroll-x -mx-4 mt-4 flex gap-2 px-4 pb-2 sm:mx-0 sm:flex-wrap sm:px-0">
        <button type="button" wire:click="$set('categoriaId', '')"
            class="fda-chip {{ $categoriaId === '' ? 'fda-chip-active' : '' }}">
            Todos
        </button>

        @foreach ($categorias as $item)
            <button type="button" wire:click="$set('categoriaId', '{{ $item->id }}')"
                class="fda-chip {{ (string) $categoriaId === (string) $item->id ? 'fda-chip-active' : '' }}">
                {{ $item->name }}
            </button>
        @endforeach
    </div>

    {{-- ======================= GRADE DE FILMES ======================= --}}
    @if ($posts->count())
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

                        @if ($post->poster_url)
                            <img src="{{ $post->poster_url }}" alt="{{ $post->name }}"
                                loading="lazy"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @elseif (file_exists(public_path('storage/capas/image'.$post->id.'.jpg')))
                            <img src="{{ url('storage/capas/image'.$post->id.'.jpg') }}" alt="{{ $post->name }}"
                                loading="lazy"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-ink-600 text-muted">
                                <i class="fas fa-film text-3xl opacity-30"></i>
                            </div>
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
                            @php
                                $primeiraCategoria = $post->categorias->first();
                            @endphp
                            @if ($primeiraCategoria)
                                <span>•</span>
                                <span class="truncate">{{ $primeiraCategoria->name }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{ $posts->links() }}
    @else
        <div class="mt-8 rounded-xl border border-dashed border-line-strong px-4 py-14 text-center text-muted">
            <i class="fas fa-film mb-3 block text-2xl opacity-70"></i>
            Nenhum filme encontrado com esses filtros.
        </div>
    @endif

</div>
