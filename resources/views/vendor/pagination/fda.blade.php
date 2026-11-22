@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginação" class="mt-12 flex justify-center">
        <ul class="flex flex-wrap items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <li>
                    <span class="block rounded-lg border border-line px-3 py-2 text-sm text-faint">Anterior</span>
                </li>
            @else
                <li>
                    <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                        class="block rounded-lg border border-line bg-ink-800 px-3 py-2 text-sm text-muted transition hover:bg-ink-700 hover:text-white disabled:opacity-50">
                        Anterior
                    </button>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>
                        <span class="block rounded-lg border border-line px-3 py-2 text-sm text-faint">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span aria-current="page"
                                    class="block rounded-lg border border-brand bg-brand px-3 py-2 text-sm font-semibold text-white">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                    wire:key="paginator-{{ $paginator->getPageName() }}-{{ $page }}"
                                    class="block rounded-lg border border-line bg-ink-800 px-3 py-2 text-sm text-muted transition hover:bg-ink-700 hover:text-white">
                                    {{ $page }}
                                </button>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li>
                    <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                        class="block rounded-lg border border-line bg-ink-800 px-3 py-2 text-sm text-muted transition hover:bg-ink-700 hover:text-white disabled:opacity-50">
                        Próximo
                    </button>
                </li>
            @else
                <li>
                    <span class="block rounded-lg border border-line px-3 py-2 text-sm text-faint">Próximo</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
