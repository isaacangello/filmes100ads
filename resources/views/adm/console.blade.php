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

    {{-- Link de download do dump SQL --}}
    @if (session('db_dump_file') && file_exists(session('db_dump_file')))
        <div class="mb-6 rounded-xl border border-green-500/30 bg-green-500/10 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <i class="fas fa-database text-green-400 mr-2"></i>
                    <strong class="text-green-400">Dump SQL disponível</strong>
                    <p class="mt-1 text-xs text-muted">
                        Arquivo: {{ session('db_dump_filename', 'filmes100ads.sql') }}
                    </p>
                </div>
                <a href="{{ route('adm.console.dump.download') }}" class="fda-btn fda-btn-primary text-xs">
                    <i class="fas fa-download text-[0.6rem]"></i> Baixar SQL
                </a>
            </div>
        </div>
    @endif

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

                            @if ($cmd['ajax'] && $key === 'db:seed-tmdb')
                                {{-- Seed TMDB com barra de progresso via AJAX --}}
                                <div x-data="tmdbSeed()">
                                    <button type="button" @click="start()" :disabled="running || done"
                                        class="fda-btn fda-btn-primary text-xs w-full" :class="{'opacity-50 cursor-not-allowed': running || done}">
                                        <template x-if="!running && !done">
                                            <span><i class="fas fa-play text-[0.6rem]"></i> Executar</span>
                                        </template>
                                        <template x-if="running">
                                            <span><i class="fas fa-spinner fa-spin text-[0.6rem]"></i> <span x-text="message"></span></span>
                                        </template>
                                        <template x-if="done">
                                            <span><i class="fas fa-check-circle text-[0.6rem] text-green-400"></i> Concluído</span>
                                        </template>
                                    </button>
                                    <template x-if="running || done">
                                        <div class="mt-3">
                                            <div class="flex justify-between text-xs text-muted mb-1">
                                                <span x-text="created + ' / ' + max + ' filmes'"></span>
                                                <span x-text="Math.round((created / max) * 100) + '%'"></span>
                                            </div>
                                            <div class="w-full bg-ink-600 rounded-full h-2.5 overflow-hidden">
                                                <div class="bg-gradient-to-r from-blue-500 to-green-500 h-2.5 rounded-full transition-all duration-300"
                                                     :style="'width: ' + ((created / max) * 100) + '%'" x-ref="bar"></div>
                                            </div>
                                            <template x-if="skipped > 0">
                                                <p class="mt-1 text-xs text-amber-400">Pulados: <span x-text="skipped"></span></p>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            @else
                                <button type="submit" class="fda-btn fda-btn-primary text-xs w-full">
                                    <i class="fas fa-play text-[0.6rem]"></i> Executar
                                </button>
                            @endif
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

<script>
// Componente Alpine.js para o seed TMDB com barra de progresso
document.addEventListener('alpine:init', () => {
    Alpine.data('tmdbSeed', () => ({
        running: false,
        done: false,
        created: 0,
        skipped: 0,
        max: 10000,
        message: '',
        pollInterval: null,

        start() {
            this.running = true;
            this.done = false;
            this.created = 0;
            this.skipped = 0;
            this.message = 'Iniciando...';

            // Inicia o seed via AJAX
            fetch('{{ route('adm.console.seed-tmdb') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                        document.querySelector('input[name="_token"]')?.value || ''
                },
                body: JSON.stringify({ max: 10000 })
            }).then(() => {
                // Inicia polling do progresso
                this.pollInterval = setInterval(() => this.poll(), 1000);
            });
        },

        poll() {
            fetch('{{ route('adm.console.seed-tmdb.progress') }}')
                .then(res => res.json())
                .then(data => {
                    this.message = data.message || '';
                    if (data.created !== undefined) this.created = data.created;
                    if (data.skipped !== undefined) this.skipped = data.skipped;
                    if (data.max !== undefined) this.max = data.max;

                    if (data.status === 'done' || data.status === 'error') {
                        this.running = false;
                        this.done = true;
                        if (this.pollInterval) clearInterval(this.pollInterval);
                    }
                })
                .catch(() => {
                    // Erro de polling — continua tentando
                });
        }
    }));
});
</script>
@endsection
