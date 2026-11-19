@extends('layouts.admin')

@section('page-title', 'Cadastrar filme')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">

    @if (isset($sucess))
        <div class="rounded-lg border border-emerald-500/35 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
            {{ $sucess }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-500/40 bg-red-500/12 px-4 py-3 text-sm text-red-300">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('cadastro.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <input type="hidden" name="tocrop" value="1">

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
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        placeholder="Ex.: Matrix" class="fda-input">
                    @error('name')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="realname" class="fda-label">Título original *</label>
                    <input id="realname" type="text" name="realname" value="{{ old('realname') }}" required
                        placeholder="Ex.: The Matrix" class="fda-input">
                    @error('realname')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="categoria" class="fda-label">Categoria *</label>
                    <select id="categoria" name="categoria" required class="fda-input">
                        <option value="">Selecione…</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(old('categoria') == $categoria->id)>{{ $categoria->name }}</option>
                        @endforeach
                    </select>
                    @error('categoria')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="sinopse" class="fda-label">Sinopse *</label>
                    <textarea id="sinopse" name="sinopse" rows="4" required
                        placeholder="Escreva uma sinopse do filme…" class="fda-input">{{ old('sinopse') }}</textarea>
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
                    <input id="diretor" type="text" name="diretor" value="{{ old('diretor') }}" required
                        placeholder="Ex.: Lana e Lilly Wachowski" class="fda-input">
                    @error('diretor')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="duracao" class="fda-label">Duração (min) *</label>
                    <input id="duracao" type="number" name="duracao" value="{{ old('duracao', 120) }}" min="1" max="600" required
                        class="fda-input">
                    @error('duracao')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="ano" class="fda-label">Ano *</label>
                    <select id="ano" name="ano" required class="fda-input">
                        <option value="">Selecione…</option>
                        @for ($i = (int) date('Y') + 1; $i >= 1910; $i--)
                            <option value="{{ $i }}" @selected(old('ano') == $i)>{{ $i }}</option>
                        @endfor
                    </select>
                    @error('ano')
                        <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sm:col-span-2 lg:col-span-4">
                    <label for="pais" class="fda-label">País de origem *</label>
                    <select id="pais" name="pais" required class="fda-input">
                        <option value="">Selecione…</option>
                        @foreach ($paises as $pais)
                            <option value="{{ $pais->nome }}" @selected(old('pais') == $pais->nome)>{{ $pais->nome }}</option>
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
                    <h2 class="fda-label mb-0">Capa do filme</h2>
                    <p class="text-xs text-muted">Você vai poder recortar a imagem na próxima etapa.</p>
                </div>
            </header>

            <div class="p-5">
                <label for="capa" class="fda-label">Escolha uma imagem *</label>
                <input id="capa" type="file" name="capa" accept="image/*" required
                    class="block w-full cursor-pointer rounded-xl border border-dashed border-line-strong bg-ink-900 px-4 py-6 text-sm text-muted transition hover:border-brand/50 hover:text-white
                           file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-soft file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white">
                @error('capa')
                    <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
                @enderror
            </div>
        </section>

        {{-- ======================= SERVIDORES ======================= --}}
        <section class="rounded-2xl border border-line bg-ink-800 shadow-card">
            <header class="flex items-center gap-3 border-b border-line px-5 py-4">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/15 text-xs text-amber-300">
                    <i class="fas fa-server"></i>
                </span>
                <div>
                    <h2 class="font-semibold">Links de servidores</h2>
                    <p class="text-xs text-muted">Cole o embed (HTML) de cada servidor. O primeiro é o padrão.</p>
                </div>
            </header>

            <div class="space-y-4 p-5">
                @for ($i = 1; $i <= 6; $i++)
                    <div class="rounded-xl border border-line bg-ink-900 p-4">
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-ink-700 text-[0.68rem] font-bold text-muted">{{ $i }}</span>
                            <label for="labelurl{{ $i }}" class="fda-label mb-0">Nome do servidor {{ $i }}</label>
                        </div>

                        <input id="labelurl{{ $i }}" type="text" name="labelurl{{ $i }}" value="{{ old('labelurl'.$i) }}"
                            placeholder="Ex.: Thevid {{ $i }}" class="fda-input mt-3">

                        <label for="url{{ $i }}" class="fda-label mt-3">Link / embed {{ $i }}</label>
                        <textarea id="url{{ $i }}" name="url{{ $i }}" rows="3"
                            placeholder="Cole aqui o código do player" class="fda-input font-mono text-xs">{{ old('url'.$i) }}</textarea>
                    </div>
                @endfor

                <p class="text-xs text-muted">
                    <i class="fas fa-circle-info mr-1"></i> O servidor 1 é obrigatório.
                </p>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3 pb-6">
            <a href="{{ route('adm') }}" class="fda-btn fda-btn-ghost">Cancelar</a>
            <button type="submit" class="fda-btn fda-btn-primary">
                <i class="fas fa-arrow-right text-xs"></i> Salvar e recortar capa
            </button>
        </div>
    </form>

</div>
@endsection
