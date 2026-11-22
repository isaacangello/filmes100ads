<div wire:key="admin-movie-form">

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-500/40 bg-red-500/12 px-4 py-3 text-sm text-red-300">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save" class="space-y-5">

        {{-- ======================= DADOS DO FILME ======================= --}}
        <section class="rounded-2xl border border-line bg-ink-800 shadow-card">
            <header class="flex items-center gap-3 border-b border-line px-5 py-4">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-soft text-xs text-brand">
                    <i class="fas fa-film"></i>
                </span>
                <div>
                    <h2 class="font-semibold">Dados do filme</h2>
                    <p class="text-xs text-muted">Informações que aparecem na página pública.</p>
                </div>
            </header>

            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="fda-label">Título do filme *</label>
                    <input id="name" type="text" wire:model="name" required autofocus
                        placeholder="Ex.: Matrix" class="fda-input @error('name') border-red-500/60 @enderror">
                    @error('name')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="realname" class="fda-label">Título original *</label>
                    <input id="realname" type="text" wire:model="realname" required
                        placeholder="Ex.: The Matrix" class="fda-input @error('realname') border-red-500/60 @enderror">
                    @error('realname')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="categoria" class="fda-label">Categoria *</label>
                    <select id="categoria" wire:model="categoria" required class="fda-input @error('categoria') border-red-500/60 @enderror">
                        <option value="">Selecione…</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->name }}</option>
                        @endforeach
                    </select>
                    @error('categoria')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="sinopse" class="fda-label">Sinopse *</label>
                    <textarea id="sinopse" wire:model="sinopse" rows="4" required
                        placeholder="Escreva uma sinopse do filme…" class="fda-input @error('sinopse') border-red-500/60 @enderror"></textarea>
                    @error('sinopse')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </section>

        {{-- ======================= FICHA TÉCNICA ======================= --}}
        <section class="rounded-2xl border border-line bg-ink-800 shadow-card">
            <header class="flex items-center gap-3 border-b border-line px-5 py-4">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/15 text-xs text-indigo-300">
                    <i class="fas fa-file-lines"></i>
                </span>
                <div>
                    <h2 class="font-semibold">Ficha técnica</h2>
                    <p class="text-xs text-muted">Diretor, duração, país de origem e ano.</p>
                </div>
            </header>

            <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2">
                    <label for="diretor" class="fda-label">Diretor *</label>
                    <input id="diretor" type="text" wire:model="diretor" required
                        placeholder="Ex.: Lana e Lilly Wachowski" class="fda-input @error('diretor') border-red-500/60 @enderror">
                    @error('diretor')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="duracao" class="fda-label">Duração (min) *</label>
                    <input id="duracao" type="number" wire:model="duracao" min="1" max="600" required
                        class="fda-input @error('duracao') border-red-500/60 @enderror">
                    @error('duracao')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="ano" class="fda-label">Ano *</label>
                    <select id="ano" wire:model="ano" required class="fda-input @error('ano') border-red-500/60 @enderror">
                        <option value="">Selecione…</option>
                        @for ($i = (int) date('Y') + 1; $i >= 1910; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    @error('ano')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sm:col-span-2 lg:col-span-4">
                    <label for="pais" class="fda-label">País de origem *</label>
                    <select id="pais" wire:model="pais" required class="fda-input @error('pais') border-red-500/60 @enderror">
                        <option value="">Selecione…</option>
                        @foreach ($paises as $item)
                            <option value="{{ $item->nome }}">{{ $item->nome }}</option>
                        @endforeach
                    </select>
                    @error('pais')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </section>

        {{-- ======================= CAPA ======================= --}}
        <section class="rounded-2xl border border-line bg-ink-800 shadow-card">
            <header class="flex items-center gap-3 border-b border-line px-5 py-4">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/15 text-xs text-emerald-400">
                    <i class="fas fa-image"></i>
                </span>
                <div>
                    <h2 class="font-semibold">Capa do filme</h2>
                    <p class="text-xs text-muted">
                        {{ $post ? 'Mantenha a capa atual ou envie uma nova imagem para recortar.' : 'Você vai poder recortar a imagem na próxima etapa.' }}
                    </p>
                </div>
            </header>

            <div class="p-5">
                <label for="capa" class="fda-label">{{ $post ? 'Nova imagem (opcional)' : 'Escolha uma imagem *' }}</label>
                <input id="capa" type="file" wire:model="capa" accept="image/*" @required(! $post)
                    class="block w-full cursor-pointer rounded-xl border border-dashed border-line-strong bg-ink-900 px-4 py-6 text-sm text-muted transition hover:border-brand/50 hover:text-white
                           file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-soft file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white">

                <div wire:loading wire:target="capa" class="mt-3 flex items-center gap-2 text-xs text-muted">
                    <i class="fas fa-circle-notch fa-spin"></i> Enviando imagem…
                </div>

                @if ($capa)
                    <div class="mt-4 flex items-center gap-3 rounded-xl border border-line bg-ink-900 p-3">
                        <img src="{{ $capa->temporaryUrl() }}" alt="Pré-visualização da capa"
                            class="h-20 w-14 rounded-lg border border-line object-cover">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ $capa->getClientOriginalName() }}</p>
                            <p class="text-xs text-muted">{{ number_format($capa->getSize() / 1024, 0) }} KB · pronto para recortar</p>
                        </div>
                    </div>
                @endif

                @error('capa')
                    <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                @enderror
            </div>
        </section>

        <div class="flex items-center justify-end gap-3 pb-6">
            <a href="{{ route('adm') }}" class="fda-btn fda-btn-ghost">Cancelar</a>

            <button type="submit" class="fda-btn fda-btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">
                    <i class="fas fa-arrow-right text-xs"></i> {{ $post ? 'Salvar alterações' : 'Salvar e recortar capa' }}
                </span>
                <span wire:loading wire:target="save">
                    <i class="fas fa-circle-notch fa-spin text-xs"></i> Salvando…
                </span>
            </button>
        </div>
    </form>

</div>
