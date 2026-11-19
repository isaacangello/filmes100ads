@extends('layouts.admin')

@section('page-title', 'Meus cadastros')

@section('content')
<div class="mx-auto max-w-6xl">

    <div class="overflow-hidden rounded-2xl border border-line bg-ink-800 shadow-card">
        <div class="flex items-center justify-between border-b border-line px-5 py-4">
            <h2 class="font-semibold">Filmes cadastrados</h2>
            <span class="text-sm text-muted">{{ $total_regs }} {{ $total_regs == 1 ? 'filme' : 'filmes' }}</span>
        </div>

        @if ($posts_user && count($posts_user))
            <div class="overflow-x-auto">
                <table class="fda-table">
                    <thead>
                        <tr>
                            <th class="w-16">#</th>
                            <th>Nome do Filme</th>
                            <th>Categoria</th>
                            <th class="w-40 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts_user as $post)
                            <tr>
                                <td class="text-muted">{{ $post->id }}</td>
                                <td class="font-medium">{{ $post->name }}</td>
                                <td class="text-muted">{{ $post->categorias }}</td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('index', ['filmeid' => $post->id]) }}"
                                            class="rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-muted transition hover:bg-ink-700 hover:text-white">
                                            Ver
                                        </a>
                                        <button type="button" disabled
                                            class="rounded-lg border border-line px-3 py-1.5 text-xs font-medium text-faint opacity-60">
                                            Editar
                                        </button>
                                        <button type="button" disabled
                                            class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs font-medium text-red-400/70 opacity-60">
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-5 py-14 text-center text-muted">
                <i class="fas fa-film mb-3 block text-2xl opacity-70"></i>
                Você ainda não cadastrou nenhum filme.
                <a href="{{ route('cadastro.index') }}" class="mt-3 block text-sm font-semibold text-white transition hover:text-brand-hover">
                    Cadastrar o primeiro
                </a>
            </div>
        @endif
    </div>

    @if ($pag)
        @php
            $totalPaginas = (int) ceil($total_regs / $exib_regs);
        @endphp

        @if ($totalPaginas > 1)
            <nav class="mt-6 flex justify-center" aria-label="Paginação">
                <ul class="flex flex-wrap items-center gap-1.5">
                    @if ($pag == 1)
                        <li><span class="block rounded-lg border border-line px-3 py-2 text-sm text-faint">Anterior</span></li>
                    @else
                        <li><a href="{{ route('meuscadastros.index', ['pag' => $pag - 1]) }}"
                                class="block rounded-lg border border-line bg-ink-800 px-3 py-2 text-sm text-muted transition hover:bg-ink-700 hover:text-white">Anterior</a></li>
                    @endif

                    @for ($i = max(1, $pag - 2); $i <= min($totalPaginas, $pag + 2); $i++)
                        @if ($i == $pag)
                            <li><span class="block rounded-lg border border-brand bg-brand px-3 py-2 text-sm font-semibold text-white">{{ $i }}</span></li>
                        @else
                            <li><a href="{{ route('meuscadastros.index', ['pag' => $i]) }}"
                                    class="block rounded-lg border border-line bg-ink-800 px-3 py-2 text-sm text-muted transition hover:bg-ink-700 hover:text-white">{{ $i }}</a></li>
                        @endif
                    @endfor

                    @if ($pag >= $totalPaginas)
                        <li><span class="block rounded-lg border border-line px-3 py-2 text-sm text-faint">Próximo</span></li>
                    @else
                        <li><a href="{{ route('meuscadastros.index', ['pag' => $pag + 1]) }}"
                                class="block rounded-lg border border-line bg-ink-800 px-3 py-2 text-sm text-muted transition hover:bg-ink-700 hover:text-white">Próximo</a></li>
                    @endif
                </ul>
            </nav>
        @endif
    @endif

</div>
@endsection
