@props([])

<div wire:poll.3s="buscar" class="space-y-6">

    {{-- Formulário de busca --}}
    <div class="flex flex-wrap gap-3">
        <div class="min-w-0 flex-1">
            <label for="torrent-busca" class="sr-only">Termo de busca</label>
            <input id="torrent-busca" type="search" wire:model.live.debounce.500ms="busca"
                placeholder="Ex.: Matrix 1999 dublado" class="fda-input" autofocus>
        </div>

        {{-- Filtro por fonte --}}
        <select wire:model.live="fonte" class="fda-input w-40">
            <option value="">Todas as fontes</option>
            @foreach ($fontesDisponiveis as $fonte)
                <option value="{{ $fonte }}">{{ $this->getFonteLabel($fonte) }}</option>
            @endforeach
        </select>

        {{-- Ordenação --}}
        <select wire:model.live="ordem" class="fda-input w-40">
            <option value="seeders">Mais seeders</option>
            <option value="size">Maior tamanho</option>
            <option value="title">Título A-Z</option>
        </select>

        @if ($busca !== '')
            <button type="button" wire:click="limparBusca" class="fda-btn fda-btn-secondary">
                <i class="fas fa-xmark text-xs"></i> Limpar
            </button>
        @endif
    </div>

    {{-- Indicador de busca --}}
    @if ($buscando)
        <div class="flex items-center justify-center gap-2 py-8">
            <i class="fas fa-spinner fa-spin text-brand"></i>
            <span class="text-sm text-muted">Buscando torrents...</span>
        </div>
    @endif

    {{-- Resultados --}}
    @if ($busca !== '' && ! $buscando)

        {{-- Erro --}}
        @if ($erro !== '')
            <div class="rounded-2xl border border-dashed border-line-strong bg-ink-850 p-8 text-center">
                <i class="fas fa-circle-exclamation mb-3 block text-2xl text-brand opacity-70"></i>
                <p class="text-sm text-muted">{{ $erro }}</p>
            </div>
        @endif

        {{-- Lista de resultados --}}
        @if (count($resultados) > 0)
            <div class="space-y-3">
                <p class="text-xs text-faint">
                    {{ count($resultados) }} resultado(s) encontrado(s) para "{{ $busca }}"
                </p>

                @foreach ($resultados as $resultado)
                    <div class="group rounded-xl border border-line bg-ink-800 p-4 transition-all hover:border-line-strong hover:shadow-card">

                        <div class="flex items-start justify-between gap-4">

                            {{-- Info principal --}}
                            <div class="min-w-0 flex-1">

                                {{-- Título e fonte --}}
                                <div class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded bg-brand-soft text-[0.6rem] text-brand">
                                        <i class="fas {{ $this->getFonteIcon($resultado['source']) }}"></i>
                                    </span>
                                    <h3 class="truncate text-sm font-semibold">
                                        {{ $resultado['title'] }}
                                    </h3>
                                </div>

                                {{-- Metadados --}}
                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted">
                                    @if ($resultado['size'] !== '' && $resultado['size'] !== '—')
                                        <span title="Tamanho">
                                            <i class="fas fa-weight-hanging text-[0.6rem]"></i>
                                            {{ $resultado['size'] }}
                                        </span>
                                    @endif

                                    @if ($resultado['seeders'] > 0)
                                        <span title="Seeders" class="text-green-400">
                                            <i class="fas fa-arrow-up text-[0.6rem]"></i>
                                            {{ $resultado['seeders'] }}
                                        </span>
                                    @endif

                                    @if ($resultado['leechers'] > 0)
                                        <span title="Leechers">
                                            <i class="fas fa-arrow-down text-[0.6rem]"></i>
                                            {{ $resultado['leechers'] }}
                                        </span>
                                    @endif

                                    @if ($resultado['category'] !== null)
                                        <span class="fda-badge" title="Categoria">
                                            {{ $resultado['category'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Ações --}}
                            <div class="flex shrink-0 items-center gap-2">

                                {{-- Link magnet --}}
                                @if ($resultado['magnet'] !== '')
                                    <a href="{{ $resultado['magnet'] }}"
                                       class="fda-btn fda-btn-primary"
                                       title="Abrir link magnet"
                                       target="_blank" rel="noopener">
                                        <i class="fas fa-magnet text-xs"></i>
                                    </a>
                                @endif

                                {{-- Link para detalhes --}}
                                @if ($resultado['url'] !== '')
                                    <a href="{{ $resultado['url'] }}"
                                       class="fda-btn fda-btn-secondary"
                                       title="Ver detalhes"
                                       target="_blank" rel="noopener">
                                        <i class="fas fa-external-link text-xs"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Estado vazio (sem busca) --}}
    @if ($busca === '' && ! $buscando)
        <div class="grid gap-4 sm:grid-cols-3">
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
