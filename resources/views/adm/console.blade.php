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

                            @if (($cmd['ajax'] ?? false) && $key === 'db:seed-tmdb')
                                {{-- Seed TMDB com barra de progresso via AJAX --}}
                                <div x-data="tmdbSeed()">
                                    <div class="flex items-center gap-2 mb-2">
                                        <label class="flex items-center gap-1.5 text-xs text-muted cursor-pointer">
                                            <input type="checkbox" x-model="incremental" class="rounded bg-ink-700 border-ink-600 text-blue-500">
                                            <span>Incremental</span>
                                        </label>
                                    </div>
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
                            @elseif ($key === 'torrents:populate')
                                {{-- Popular torrents --}}
                                <div x-data="torrentPopulate()">
                                    <button type="button" @click="start()" :disabled="running || done"
                                        class="fda-btn fda-btn-primary text-xs w-full" :class="{'opacity-50 cursor-not-allowed': running || done}">
                                        <template x-if="!running && !done">
                                            <span><i class="fas fa-download text-[0.6rem]"></i> Executar</span>
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
                                                <span x-text="processed + ' / ' + total + ' filmes'"></span>
                                                <span x-text="Math.round((processed / total) * 100) + '%'"></span>
                                            </div>
                                            <div class="w-full bg-ink-600 rounded-full h-2.5 overflow-hidden">
                                                <div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2.5 rounded-full transition-all duration-300"
                                                     :style="'width: ' + ((processed / total) * 100) + '%'" x-ref="bar"></div>
                                            </div>
                                            <p class="mt-1 text-xs text-green-400">Torrents: <span x-text="torrents_saved"></span></p>
                                        </div>
                                    </template>
                                </div>
                            @elseif ($key === 'torrents:reset')
                                {{-- Reset torrents --}}
                                <div x-data="torrentReset()">
                                    <button type="button" @click="start()" :disabled="running || done"
                                        class="fda-btn bg-red-600 hover:bg-red-500 border-red-600 text-xs w-full" :class="{'opacity-50 cursor-not-allowed': running || done}">
                                        <template x-if="!running && !done">
                                            <span><i class="fas fa-trash text-[0.6rem]"></i> Resetar torrents</span>
                                        </template>
                                        <template x-if="running">
                                            <span><i class="fas fa-spinner fa-spin text-[0.6rem]"></i> <span x-text="message"></span></span>
                                        </template>
                                        <template x-if="done">
                                            <span><i class="fas fa-check-circle text-[0.6rem] text-green-400"></i> Concluído</span>
                                        </template>
                                    </button>
                                </div>
                            @elseif ($key === 'db:seed-tmdb-reset')
                                {{-- Reset TMDB --}}
                                <div x-data="tmdbSeedReset()">
                                    <button type="button" @click="start()" :disabled="running || done"
                                        class="fda-btn bg-red-600 hover:bg-red-500 border-red-600 text-xs w-full" :class="{'opacity-50 cursor-not-allowed': running || done}">
                                        <template x-if="!running && !done">
                                            <span><i class="fas fa-trash text-[0.6rem]"></i> Resetar tudo</span>
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
                                                <div class="bg-gradient-to-r from-red-500 to-orange-500 h-2.5 rounded-full transition-all duration-300"
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
// Componente Alpine.js para o seed TMDB por lotes via AJAX
// Cada lote processa uma página da TMDB (~20 filmes) e retorna em ~10-15 segundos
// Isso evita o timeout de 60s do InfinityFree
document.addEventListener('alpine:init', () => {
    // Seed incremental — adiciona apenas filmes novos
    Alpine.data('tmdbSeed', () => ({
        running: false,
        done: false,
        created: 0,
        skipped: 0,
        max: 10000,
        message: '',
        incremental: true,
        batchTimeout: null,
        csrfToken: '',

        start() {
            this.running = true;
            this.done = false;
            this.created = 0;
            this.skipped = 0;
            this.message = 'Iniciando...';
            this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('input[name="_token"]')?.value || '';

            this.initSeed();
        },

        initSeed() {
            fetch('{{ route('adm.console.seed-tmdb') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ max: 10000, incremental: this.incremental })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    this.message = data.message || 'Erro ao inicializar.';
                    this.running = false;
                    this.done = true;
                    return;
                }
                this.max = data.max || 10000;
                this.message = data.message || 'Inicializado. Processando lotes...';
                this.processBatch();
            })
            .catch(err => {
                this.message = 'Erro de conexão.';
                this.running = false;
                this.done = true;
            });
        },

        processBatch() {
            if (!this.running) return;

            this.message = 'Processando lote... (página carregando)';

            fetch('{{ route('adm.console.seed-tmdb.batch') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    this.message = data.message || 'Erro no lote.';
                    this.running = false;
                    this.done = true;
                    return;
                }

                this.created = data.created || this.created;
                this.skipped = data.skipped || this.skipped;
                this.max = data.max || this.max;
                this.message = data.message || `Lote ${data.page || '?'} processado`;

                if (data.status === 'done') {
                    this.running = false;
                    this.done = true;
                    this.message = data.message;
                } else {
                    this.batchTimeout = setTimeout(() => this.processBatch(), 500);
                }
            })
            .catch(err => {
                this.message = 'Erro de conexão. Retentando...';
                this.batchTimeout = setTimeout(() => this.processBatch(), 5000);
            });
        }
    }));

    // Reset — apaga tudo e recomeça do zero
    Alpine.data('tmdbSeedReset', () => ({
        running: false,
        done: false,
        created: 0,
        skipped: 0,
        max: 10000,
        message: '',
        batchTimeout: null,
        csrfToken: '',

        start() {
            this.running = true;
            this.done = false;
            this.created = 0;
            this.skipped = 0;
            this.message = 'Resetando...';
            this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('input[name="_token"]')?.value || '';

            this.initSeed();
        },

        initSeed() {
            fetch('{{ route('adm.console.seed-tmdb') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ max: 10000, incremental: false })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    this.message = data.message || 'Erro ao inicializar.';
                    this.running = false;
                    this.done = true;
                    return;
                }
                this.max = data.max || 10000;
                this.message = data.message || 'Reset concluído. Processando lotes...';
                this.processBatch();
            })
            .catch(err => {
                this.message = 'Erro de conexão.';
                this.running = false;
                this.done = true;
            });
        },

        processBatch() {
            if (!this.running) return;

            this.message = 'Processando lote...';

            fetch('{{ route('adm.console.seed-tmdb.batch') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    this.message = data.message || 'Erro no lote.';
                    this.running = false;
                    this.done = true;
                    return;
                }

                this.created = data.created || this.created;
                this.skipped = data.skipped || this.skipped;
                this.max = data.max || this.max;
                this.message = data.message || `Lote ${data.page || '?'} processado`;

                if (data.status === 'done') {
                    this.running = false;
                    this.done = true;
                    this.message = data.message;
                } else {
                    this.batchTimeout = setTimeout(() => this.processBatch(), 500);
                }
            })
            .catch(err => {
                this.message = 'Erro de conexão. Retentando...';
                this.batchTimeout = setTimeout(() => this.processBatch(), 5000);
            });
        }
    }));

    // Populador de torrents — busca torrents para todos os filmes
    Alpine.data('torrentPopulate', () => ({
        running: false,
        done: false,
        processed: 0,
        total: 0,
        torrents_saved: 0,
        message: '',
        batchTimeout: null,
        csrfToken: '',

        start() {
            this.running = true;
            this.done = false;
            this.processed = 0;
            this.torrents_saved = 0;
            this.message = 'Iniciando...';
            this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('input[name="_token"]')?.value || '';

            this.initPopulate();
        },

        initPopulate() {
            fetch('{{ route('adm.console.populate-torrents') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    this.message = data.message || 'Erro ao inicializar.';
                    this.running = false;
                    this.done = true;
                    return;
                }
                this.total = data.total || 0;
                this.message = data.message || 'Inicializado. Processando lotes...';
                this.processBatch();
            })
            .catch(err => {
                this.message = 'Erro de conexão.';
                this.running = false;
                this.done = true;
            });
        },

        processBatch() {
            if (!this.running) return;

            this.message = 'Buscando torrents...';

            fetch('{{ route('adm.console.populate-torrents.batch') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    this.message = data.message || 'Erro no lote.';
                    this.running = false;
                    this.done = true;
                    return;
                }

                this.processed = data.processed || this.processed;
                this.total = data.total || this.total;
                this.torrents_saved = data.torrents_saved || this.torrents_saved;
                this.message = data.message || 'Processando...';

                if (data.status === 'done') {
                    this.running = false;
                    this.done = true;
                    this.message = data.message;
                } else {
                    this.batchTimeout = setTimeout(() => this.processBatch(), 500);
                }
            })
            .catch(err => {
                this.message = 'Erro de conexão. Retentando...';
                this.batchTimeout = setTimeout(() => this.processBatch(), 5000);
            });
        }
    }));

    // Reset de torrents — apaga todos os torrents salvos
    Alpine.data('torrentReset', () => ({
        running: false,
        done: false,
        message: '',
        csrfToken: '',

        start() {
            if (!confirm('Tem certeza que deseja apagar todos os torrents salvos?')) return;

            this.running = true;
            this.done = false;
            this.message = 'Apagando torrents...';
            this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('input[name="_token"]')?.value || '';

            fetch('{{ route('adm.console.execute') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ command: 'torrents:reset' })
            })
            .then(res => res.json())
            .then(data => {
                this.message = data.message || 'Torrents apagados.';
                this.running = false;
                this.done = true;
            })
            .catch(err => {
                this.message = 'Erro ao resetar.';
                this.running = false;
                this.done = true;
            });
        }
    }));
});
</script>
@endsection
