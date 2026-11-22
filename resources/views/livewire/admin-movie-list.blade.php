<div wire:key="admin-movie-list" x-data="{ deleteModal: false, deleteId: null, deleteName: '' }">

    {{-- Mensagem de feedback --}}
    @if ($mensagem !== '')
        <div class="mb-4 rounded-xl border border-green-500/30 bg-green-500/10 p-3 text-sm text-green-400"
             x-data="{ show: true }" x-show="show" x-transition>
            <i class="fas fa-check-circle mr-2"></i>
            {{ $mensagem }}
            <button type="button" class="ml-2 text-xs opacity-70 hover:opacity-100"
                    @click="show = false; $wire.mensagem = ''" wire:ignore>
                <i class="fas fa-xmark"></i>
            </button>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-line bg-ink-800 shadow-card">

        {{-- ======================= CABEÇALHO ======================= --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
            <div>
                <h2 class="font-semibold">Filmes cadastrados</h2>
                <p class="mt-0.5 text-xs text-muted">
                    {{ number_format($filmes->total()) }} {{ $filmes->total() == 1 ? 'filme' : 'filmes' }} publicados por você
                </p>
            </div>

            <div class="relative w-full sm:w-64">
                <i class="fas fa-magnifying-glass pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-xs text-faint"></i>
                <input type="search" wire:model.live.debounce.400ms="busca" placeholder="Buscar nos meus filmes…"
                    class="fda-input pl-9" aria-label="Buscar nos meus filmes">
            </div>
        </div>

        {{-- ======================= FILTROS ======================= --}}
        <div class="flex flex-wrap items-center gap-3 border-b border-line px-5 py-3 bg-ink-850">
            <select wire:model.live="status1" class="fda-input w-40 text-xs">
                @foreach ($this->getStatus1Opcoes() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model.live="status2" class="fda-input w-40 text-xs">
                @foreach ($this->getStatus2Opcoes() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            @if ($status1 !== '' || $status2 !== '' || $busca !== '')
                <button type="button" wire:click="limparFiltros" class="fda-btn fda-btn-secondary text-xs">
                    <i class="fas fa-xmark text-[0.6rem]"></i> Limpar filtros
                </button>
            @endif
        </div>

        {{-- ======================= TABELA ======================= --}}
        @if ($filmes->count())
            <div class="overflow-x-auto">
                <table class="fda-table">
                    <thead>
                        <tr>
                            <th class="w-16">#</th>
                            <th>Nome do Filme</th>
                            <th class="hidden sm:table-cell">Categoria</th>
                            <th class="hidden md:table-cell">Status</th>
                            <th class="hidden md:table-cell">Cadastrado em</th>
                            <th class="w-40 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($filmes as $filme)
                            <tr wire:key="filme-{{ $filme->id }}">
                                <td class="text-muted">{{ $filme->id }}</td>
                                <td class="font-medium">
                                    <a href="{{ route('index', ['filmeid' => $filme->id]) }}"
                                        class="transition hover:text-brand-hover">{{ $filme->name }}</a>
                                </td>
                                <td class="hidden text-muted sm:table-cell">{{ $filme->categorias }}</td>
                                <td class="hidden md:table-cell">
                                    <span class="fda-badge text-[0.6rem]">
                                        {{ ucfirst($filme->status1 ?? '—') }}
                                    </span>
                                    @if ($filme->status2)
                                        <span class="fda-badge text-[0.6rem] ml-1">
                                            {{ ucfirst($filme->status2) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="hidden text-muted md:table-cell">
                                    {{ $filme->created_at ? \Illuminate\Support\Carbon::parse($filme->created_at)->format('d/m/Y') : '—' }}
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('index', ['filmeid' => $filme->id]) }}"
                                            class="rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-muted transition hover:bg-ink-700 hover:text-white">
                                            Ver
                                        </a>
                                        <a href="{{ route('adm.movies.edit', $filme->id) }}"
                                            class="rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-muted transition hover:bg-ink-700 hover:text-white">
                                            Editar
                                        </a>
                                        <button type="button"
                                                @click="deleteModal = true; deleteId = {{ $filme->id }}; deleteName = '{{ addslashes($filme->name) }}'"
                                                class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10"
                                                wire:ignore>
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-line px-5 py-4">
                {{ $filmes->links() }}
            </div>
        @else
            <div class="px-5 py-14 text-center text-muted">
                <i class="fas fa-film mb-3 block text-2xl opacity-70"></i>
                @if (trim((string) $busca) !== '' || $status1 !== '' || $status2 !== '')
                    Nenhum filme encontrado com os filtros aplicados.
                @else
                    Você ainda não cadastrou nenhum filme.
                    <a href="{{ route('cadastro.index') }}" class="mt-3 block text-sm font-semibold text-white transition hover:text-brand-hover">
                        Cadastrar o primeiro
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- ======================= MODAL DE CONFIRMAÇÃO ======================= --}}
    <template x-if="deleteModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" x-transition>
            <div class="w-full max-w-md rounded-2xl border border-line bg-ink-800 p-6 shadow-card"
                 @click.away="deleteModal = false">
                <h3 class="text-lg font-semibold">Confirmar exclusão</h3>
                <p class="mt-2 text-sm text-muted">
                    Tem certeza que deseja excluir <strong x-text="deleteName"></strong>?
                    Esta ação não pode ser desfeita.
                </p>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button"
                            @click="deleteModal = false"
                            class="fda-btn fda-btn-secondary">
                        Cancelar
                    </button>
                    <button type="button"
                            @click="$wire.excluir(deleteId).then(() => { deleteModal = false; deleteId = null; deleteName = ''; })"
                            class="fda-btn bg-red-600 hover:bg-red-500 border-red-600">
                        <i class="fas fa-trash text-xs"></i> Excluir
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
