@extends('layouts.admin')

@section('title', 'Console de comandos')
@section('page-title', 'Console')

@section('content')
<div class="mx-auto max-w-4xl">

    {{-- Mensagem de erro do middleware --}}
    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-400">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6">
        <h2 class="text-xl font-semibold">Console de comandos</h2>
        <p class="mt-1 text-sm text-muted">
            Execute comandos Artisan essenciais para manter o site funcionando.
            Alguns comandos podem levar alguns minutos.
        </p>
    </div>

    {{-- Resultado do último comando --}}
    @if ($lastOutput !== null)
        <div class="mb-6 overflow-hidden rounded-2xl border border-line bg-ink-800 shadow-card">
            <div class="flex items-center justify-between border-b border-line px-5 py-3">
                <div class="flex items-center gap-2">
                    @if ($lastExitCode === 0)
                        <i class="fas fa-check-circle text-green-400"></i>
                        <span class="text-sm font-medium text-green-400">{{ $lastCommandName }} — concluído</span>
                    @else
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                        <span class="text-sm font-medium text-red-400">{{ $lastCommandName }} — falhou</span>
                    @endif
                </div>
                <button type="button" x-data @click="$el.closest('.rounded-2xl').remove()"
                    class="text-xs text-muted transition hover:text-white">
                    <i class="fas fa-xmark"></i> Fechar
                </button>
            </div>
            <div class="max-h-64 overflow-y-auto p-4">
                <pre class="whitespace-pre-wrap text-xs leading-relaxed text-muted">{{ $lastOutput }}</pre>
            </div>
        </div>
    @endif

    {{-- Cards de comandos --}}
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($commands as $key => $cmd)
            <div class="overflow-hidden rounded-2xl border border-line bg-ink-800 shadow-card">
                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold">
                                {{ $cmd['name'] }}
                                @if ($cmd['dangerous'])
                                    <span class="ml-2 rounded-full bg-red-500/20 px-2 py-0.5 text-[0.6rem] font-bold text-red-400">
                                        PERIGOSO
                                    </span>
                                @endif
                            </h3>
                            <p class="mt-1 text-xs text-muted">{{ $cmd['description'] }}</p>
                        </div>
                        <i class="fas fa-terminal text-muted"></i>
                    </div>
                    <div class="mt-4">
                        <form action="{{ route('adm.console.execute') }}" method="POST"
                            x-data="{ confirming: false }"
                            @if ($cmd['dangerous'])
                                @submit="confirming = true"
                            @endif
                        >
                            @csrf
                            <input type="hidden" name="command" value="{{ $key }}">

                            @if ($cmd['dangerous'])
                                <template x-if="confirming">
                                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" x-transition>
                                        <div class="w-full max-w-md rounded-2xl border border-red-500/30 bg-ink-800 p-6 shadow-card"
                                             @click.away="confirming = false">
                                            <h4 class="text-lg font-semibold text-red-400">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                Confirmação necessária
                                            </h4>
                                            <p class="mt-2 text-sm text-muted">
                                                "{{ $cmd['name'] }}" é um comando destrutivo. Tem certeza que deseja executá-lo?
                                            </p>
                                            <div class="mt-5 flex justify-end gap-3">
                                                <button type="button"
                                                        @click="confirming = false"
                                                        class="fda-btn fda-btn-secondary">
                                                    Cancelar
                                                </button>
                                                <button type="submit"
                                                        class="fda-btn bg-red-600 hover:bg-red-500 border-red-600">
                                                    <i class="fas fa-exclamation-triangle text-xs"></i> Executar mesmo assim
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            @endif

                            <button type="submit" class="fda-btn fda-btn-primary text-xs w-full">
                                <i class="fas fa-play text-[0.6rem]"></i> Executar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Aviso --}}
    <div class="mt-6 rounded-xl border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-300">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>Atenção:</strong> Os comandos de migração e seed são destrutivos. Use com cuidado em produção.
        O comando <code class="bg-ink-700 px-1 py-0.5 rounded">db:populate-posters</code> pode levar vários minutos dependendo do número de filmes.
    </div>
</div>
@endsection
