<div wire:key="user-manager" x-data="{ deleteModal: false, deleteId: null, deleteName: '' }">

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
                <h2 class="font-semibold">Gerenciar usuários</h2>
                <p class="mt-0.5 text-xs text-muted">
                    {{ number_format($usuarios->total()) }} {{ $usuarios->total() == 1 ? 'usuário' : 'usuários' }} no sistema
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('adm.users.create') }}" class="fda-btn fda-btn-primary text-xs">
                    <i class="fas fa-plus text-[0.6rem]"></i> Novo usuário
                </a>

                <div class="relative w-full sm:w-64">
                    <i class="fas fa-magnifying-glass pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-xs text-faint"></i>
                    <input type="search" wire:model.live.debounce.400ms="busca" placeholder="Buscar por nome ou email…"
                        class="fda-input pl-9" aria-label="Buscar usuários">
                </div>
            </div>
        </div>

        {{-- ======================= TABELA ======================= --}}
        @if ($usuarios->count())
            <div class="overflow-x-auto">
                <table class="fda-table">
                    <thead>
                        <tr>
                            <th class="w-16">#</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th class="hidden sm:table-cell">Admin</th>
                            <th class="hidden md:table-cell">Criado em</th>
                            <th class="w-48 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            <tr wire:key="user-{{ $usuario->id }}">
                                <td class="text-muted">{{ $usuario->id }}</td>
                                <td class="font-medium">{{ $usuario->name }}</td>
                                <td class="text-muted">{{ $usuario->email }}</td>
                                <td class="hidden sm:table-cell">
                                    @if ($usuario->is_admin)
                                        <span class="fda-badge bg-amber-500/20 text-amber-300 text-[0.6rem]">
                                            <i class="fas fa-shield-halved mr-1 text-[0.55rem]"></i> Admin
                                        </span>
                                    @else
                                        <span class="fda-badge text-[0.6rem]">
                                            Usuário
                                        </span>
                                    @endif
                                </td>
                                <td class="hidden text-muted md:table-cell">
                                    {{ $usuario->created_at ? \Illuminate\Support\Carbon::parse($usuario->created_at)->format('d/m/Y') : '—' }}
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('adm.users.edit', $usuario->id) }}"
                                           class="rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-muted transition hover:bg-ink-700 hover:text-white">
                                            <i class="fas fa-pen text-[0.6rem]"></i>
                                            Editar
                                        </a>
                                        <button type="button"
                                                wire:click="toggleAdmin({{ $usuario->id }})"
                                                wire:confirm="Tem certeza que deseja {{ $usuario->is_admin ? 'remover' : 'adicionar' }} o status de admin de '{{ $usuario->name }}'?"
                                                class="rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-muted transition hover:bg-ink-700 hover:text-white"
                                                @if ($usuario->id === auth()->id()) disabled title="Você não pode alterar o seu próprio status" @endif>
                                            <i class="fas fa-shield-halved text-[0.6rem]"></i>
                                            {{ $usuario->is_admin ? 'Remover admin' : 'Tornar admin' }}
                                        </button>
                                        <button type="button"
                                                @click="deleteModal = true; deleteId = {{ $usuario->id }}; deleteName = '{{ addslashes($usuario->name) }}'"
                                                class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:bg-red-500/10"
                                                wire:ignore
                                                @if ($usuario->id === auth()->id()) disabled title="Você não pode excluir a si mesmo" @endif>
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
                {{ $usuarios->links() }}
            </div>
        @else
            <div class="px-5 py-14 text-center text-muted">
                <i class="fas fa-users mb-3 block text-2xl opacity-70"></i>
                @if (trim((string) $busca) !== '')
                    Nenhum usuário encontrado com os filtros aplicados.
                @else
                    Nenhum usuário cadastrado no sistema.
                    <a href="{{ route('adm.users.create') }}" class="mt-3 block text-sm font-semibold text-white transition hover:text-brand-hover">
                        Criar o primeiro usuário
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
