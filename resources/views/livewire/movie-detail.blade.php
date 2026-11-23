<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

    <a href="{{ route('index') }}" class="mb-5 inline-flex items-center gap-2 text-sm text-muted transition hover:text-white">
        <i class="fas fa-arrow-left text-xs"></i> Voltar ao catálogo
    </a>

    {{-- ======================= DESTAQUE ======================= --}}
    <div class="grid gap-8 rounded-2xl border border-line bg-gradient-to-br from-ink-800 to-ink-900 p-6 shadow-card lg:grid-cols-[260px_1fr] lg:p-7">
        <div class="overflow-hidden rounded-xl border border-line bg-ink-700">
            @if ($filme->poster_url)
                <img src="{{ $filme->poster_url }}" alt="{{ $filme->name }}" class="w-full">
            @elseif (file_exists(public_path('storage/capas/image'.$filme->id.'.jpg')))
                <img src="{{ url('storage/capas/image'.$filme->id.'.jpg') }}" alt="{{ $filme->name }}" class="w-full">
            @else
                <div class="flex h-full min-h-[300px] items-center justify-center bg-ink-600 text-muted">
                    <i class="fas fa-film text-4xl opacity-30"></i>
                </div>
            @endif
        </div>

        <div>
            <h1 class="text-3xl font-extrabold tracking-tight lg:text-4xl">{{ $filme->name }}</h1>

            @if ($filme->realname)
                <p class="mt-1.5 text-sm text-muted">Título original: {{ $filme->realname }}</p>
            @endif

            <div class="mt-5 flex flex-wrap gap-2">
                @if ($categoria)
                    <span class="fda-badge"><i class="fas fa-tag text-[0.7rem] text-brand"></i> {{ $categoria }}</span>
                @endif
                @if ($filme->ano)
                    <span class="fda-badge"><i class="fas fa-calendar text-[0.7rem] text-brand"></i> {{ $filme->ano }}</span>
                @endif
                @if ($filme->duracao)
                    <span class="fda-badge"><i class="fas fa-clock text-[0.7rem] text-brand"></i> {{ $filme->duracao }} min</span>
                @endif
                @if ($filme->pais)
                    <span class="fda-badge"><i class="fas fa-globe text-[0.7rem] text-brand"></i> {{ $filme->pais }}</span>
                @endif
                @if ($filme->diretor)
                    <span class="fda-badge"><i class="fas fa-user text-[0.7rem] text-brand"></i> {{ $filme->diretor }}</span>
                @endif
                @if ($filme->vote_average)
                    <span class="fda-badge" style="background:rgba(255,193,7,.12);color:#fbbf24;">
                        <i class="fas fa-star text-[0.7rem]"></i> {{ number_format((float) $filme->vote_average, 1, ',', '.') }} / 10
                    </span>
                @endif
                @if ($filme->original_language)
                    <span class="fda-badge"><i class="fas fa-language text-[0.7rem] text-brand"></i> {{ strtoupper($filme->original_language) }}</span>
                @endif
            </div>

            @if ($filme->sinopse)
                <p class="mt-5 text-[0.96rem] leading-relaxed text-[#c9c9d4]">{{ $filme->sinopse }}</p>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                @if ($magnetLink || count($torrentResults) > 0)
                    <a href="#torrents"
                        class="fda-btn fda-btn-primary">
                        <i class="fas fa-download text-xs"></i> Download Torrent
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ======================= ELENCO ======================= --}}
    @if ($filme->cast->isNotEmpty())
        <div class="mt-8 border-t border-line pt-8">
            <h2 class="mb-5 text-xl font-bold flex items-center gap-2">
                <i class="fas fa-users text-brand"></i> Elenco
            </h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                @foreach ($filme->cast as $ator)
                    <div class="group rounded-xl border border-line bg-ink-800 p-3 transition-all hover:border-line-strong hover:shadow-card">
                        @if ($ator->profile_path)
                            <img src="https://image.tmdb.org/t/p/w185{{ $ator->profile_path }}"
                                alt="{{ $ator->name }}"
                                class="aspect-[2/3] w-full rounded-lg object-cover"
                                loading="lazy">
                        @else
                            <div class="aspect-[2/3] w-full rounded-lg bg-ink-700 flex items-center justify-center">
                                <i class="fas fa-user text-2xl text-muted opacity-30"></i>
                            </div>
                        @endif
                        <div class="mt-3">
                            <p class="text-sm font-semibold truncate">{{ $ator->name }}</p>
                            @if ($ator->character)
                                <p class="text-xs text-muted truncate">{{ $ator->character }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ======================= DOWNLOAD TORRENT ======================= --}}
    <div id="torrents" class="mt-8 border-t border-line pt-8">
        <h2 class="mb-5 text-xl font-bold flex items-center gap-2">
            <i class="fas fa-download text-brand"></i> Downloads
        </h2>

        {{-- Torrents salvos agrupados por qualidade --}}
        @if ($torrentsByQuality->isNotEmpty())
            @php
                // Ordem de qualidade: 4K > 1080p > 720p > 480p > BluRay > WEB-DL > null
                $qualityOrder = ['4K' => 1, '1080p' => 2, '720p' => 3, '480p' => 4, 'BluRay' => 5, 'WEB-DL' => 6, null => 7];
                $qualityLabels = [
                    '4K' => '<i class="fas fa-crown text-[0.6rem]"></i> 4K Ultra HD',
                    '1080p' => '<i class="fas fa-video text-[0.6rem]"></i> 1080p Full HD',
                    '720p' => '<i class="fas fa-film text-[0.6rem]"></i> 720p HD',
                    '480p' => '<i class="fas fa-mobile-alt text-[0.6rem]"></i> 480p SD',
                    'BluRay' => '<i class="fas fa-compact-disc text-[0.6rem]"></i> BluRay',
                    'WEB-DL' => '<i class="fas fa-cloud-download-alt text-[0.6rem]"></i> WEB-DL',
                ];
                $qualityColors = [
                    '4K' => 'from-purple-500/20 to-purple-900/20 border-purple-500/30',
                    '1080p' => 'from-blue-500/20 to-blue-900/20 border-blue-500/30',
                    '720p' => 'from-green-500/20 to-green-900/20 border-green-500/30',
                    '480p' => 'from-yellow-500/20 to-yellow-900/20 border-yellow-500/30',
                    'BluRay' => 'from-cyan-500/20 to-cyan-900/20 border-cyan-500/30',
                    'WEB-DL' => 'from-orange-500/20 to-orange-900/20 border-orange-500/30',
                ];
            @endphp

            @foreach ($torrentsByQuality->sortBy(function ($items, $quality) use ($qualityOrder) {
                return $qualityOrder[$quality] ?? 7;
            }) as $quality => $torrents)
                <div class="mb-6">
                    <h3 class="mb-3 text-sm font-bold text-muted uppercase tracking-wider">
                        {!! $qualityLabels[$quality] ?? '<i class="fas fa-question text-[0.6rem]"></i> Outros' !!}
                    </h3>
                    <div class="space-y-2">
                        @foreach ($torrents as $torrent)
                            <div class="group flex items-center justify-between gap-4 rounded-xl border border-line bg-gradient-to-r {{ $qualityColors[$quality] ?? 'from-ink-500/10 to-ink-900/10 border-line' }} p-4 transition-all hover:border-line-strong hover:shadow-card">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">{{ $torrent->title }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted">
                                        @if ($torrent->size)
                                            <span><i class="fas fa-weight-hanging text-[0.6rem]"></i> {{ $torrent->size }}</span>
                                        @endif
                                        @if ($torrent->seeders > 0)
                                            <span class="text-green-400"><i class="fas fa-arrow-up text-[0.6rem]"></i> {{ $torrent->seeders }}</span>
                                        @endif
                                        @if ($torrent->leechers > 0)
                                            <span><i class="fas fa-arrow-down text-[0.6rem]"></i> {{ $torrent->leechers }}</span>
                                        @endif
                                        @if ($torrent->source)
                                            <span class="rounded bg-ink-700 px-1.5 py-0.5 text-[0.6rem]">{{ $torrent->source }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div x-data="torrentDelay('{{ $torrent->magnet }}', 10)" class="shrink-0">
                                    <button type="button" @click="start()" :disabled="counting || done"
                                        class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-bold text-white transition-all"
                                        :class="counting ? 'bg-brand/50 cursor-wait' : 'bg-brand hover:-translate-y-0.5 hover:bg-brand-hover'"
                                        :style="counting ? 'opacity:0.8' : ''">
                                        <template x-if="!counting && !done">
                                            <span><i class="fas fa-magnet"></i> Baixar</span>
                                        </template>
                                        <template x-if="counting">
                                            <span><i class="fas fa-hourglass-half fa-spin"></i> <span x-text="seconds"></span>s</span>
                                        </template>
                                        <template x-if="done">
                                            <span><i class="fas fa-check"></i> Pronto!</span>
                                        </template>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @elseif ($magnetLink)
            {{-- Magnet link legado (único) --}}
            <div class="rounded-2xl border-2 border-brand/30 bg-gradient-to-br from-brand-soft/20 to-ink-900 p-8 text-center">
                <div class="mx-auto max-w-md">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-brand/20">
                        <i class="fas fa-download text-2xl text-brand"></i>
                    </div>
                    <h2 class="text-2xl font-bold">Download do Filme</h2>
                    <p class="mt-2 text-sm text-muted">Clique no botão abaixo para abrir o link magnet no seu cliente de torrent.</p>
                    <div x-data="torrentDelay('{{ $magnetLink }}', 10)">
                        <button type="button" @click="start()" :disabled="counting || done"
                            class="mt-6 inline-flex items-center gap-3 rounded-xl px-8 py-4 text-lg font-bold text-white shadow-lg transition-all"
                            :class="counting ? 'bg-brand/50 cursor-wait' : 'bg-brand hover:-translate-y-0.5 hover:bg-brand-hover hover:shadow-xl'"
                            :style="counting ? 'opacity:0.8' : ''">
                            <template x-if="!counting && !done">
                                <span><i class="fas fa-magnet text-xl"></i> Baixar via Torrent</span>
                            </template>
                            <template x-if="counting">
                                <span><i class="fas fa-hourglass-half fa-spin text-xl"></i> Aguarde <span x-text="seconds"></span>s</span>
                            </template>
                            <template x-if="done">
                                <span><i class="fas fa-check text-xl"></i> Download liberado!</span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        @elseif (count($torrentResults) > 0)
            {{-- Lista de resultados da busca em tempo real --}}
            <div class="space-y-3">
                <p class="text-xs text-faint">
                    {{ count($torrentResults) }} resultado(s) encontrado(s)
                </p>
                @foreach ($torrentResults as $resultado)
                    <div class="group rounded-xl border border-line bg-ink-800 p-4 transition-all hover:border-line-strong hover:shadow-card">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded bg-brand-soft text-[0.6rem] text-brand">
                                        <i class="fas fa-download"></i>
                                    </span>
                                    <h3 class="truncate text-sm font-semibold">
                                        {{ $resultado['title'] }}
                                    </h3>
                                </div>
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
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                @if ($resultado['magnet'] !== '')
                                    @php
                                        $magnetValue = $resultado['magnet'];
                                    @endphp
                                    <div x-data="torrentDelay('{{ $magnetValue }}', 10)">
                                        <button type="button" @click="start()" :disabled="counting || done"
                                            class="fda-btn fda-btn-primary"
                                            :class="counting ? 'opacity-50 cursor-wait' : ''"
                                            title="Abrir link magnet">
                                            <template x-if="!counting && !done">
                                                <span><i class="fas fa-magnet text-xs"></i></span>
                                            </template>
                                            <template x-if="counting">
                                                <span><i class="fas fa-hourglass-half fa-spin text-xs"></i> <span x-text="seconds"></span>s</span>
                                            </template>
                                            <template x-if="done">
                                                <span><i class="fas fa-check text-xs"></i></span>
                                            </template>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Nenhum torrent encontrado --}}
            <div class="rounded-xl border border-dashed border-line bg-ink-800 p-6 text-center">
                <i class="fas fa-circle-exclamation mb-2 block text-xl text-muted opacity-50"></i>
                <p class="text-sm text-muted">Nenhum torrent encontrado para este filme.</p>
                <p class="mt-1 text-xs text-faint">Use o console ADM para popular os torrents.</p>
            </div>
        @endif
    </div>

    {{-- ======================= ÚLTIMOS ADICIONADOS ======================= --}}
    @if ($ultimosAdicionados->isNotEmpty())
        <div class="mt-8 border-t border-line pt-8">
            <h2 class="mb-5 text-xl font-bold flex items-center gap-2">
                <i class="fas fa-clock text-brand"></i> Últimos adicionados em {{ date('Y') }}
            </h2>
            <div class="grid grid-cols-2 gap-x-3.5 gap-y-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                @foreach ($ultimosAdicionados as $post)
                    <a href="{{ route('index', ['filmeid' => $post->id]) }}" title="{{ $post->name }}"
                        class="group block transition hover:-translate-y-1">
                        <div class="relative aspect-[2/3] overflow-hidden rounded-xl border border-line bg-ink-700 shadow-card transition group-hover:border-line-strong group-hover:shadow-card-hover">
                            @if ($post->poster_url)
                                <img src="{{ $post->poster_url }}" alt="{{ $post->name }}"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @elseif (file_exists(public_path('storage/capas/image'.$post->id.'.jpg')))
                                <img src="{{ url('storage/capas/image'.$post->id.'.jpg') }}" alt="{{ $post->name }}"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-ink-600 text-muted">
                                    <i class="fas fa-film text-3xl opacity-30"></i>
                                </div>
                            @endif
                        </div>
                        <div class="px-0.5 pt-2.5">
                            <h3 class="line-clamp-2 text-sm leading-snug font-semibold">{{ $post->name }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>

{{-- Script do delay de torrent com mineração --}}
<script>
function torrentDelay(magnet, delaySeconds) {
    return {
        seconds: delaySeconds,
        counting: false,
        done: false,
        interval: null,

        start() {
            if (this.counting || this.done) return;
            this.counting = true;
            this.seconds = delaySeconds;

            this.interval = setInterval(() => {
                this.seconds--;
                if (this.seconds <= 0) {
                    clearInterval(this.interval);
                    this.counting = false;
                    this.done = true;
                    // Abrir o magnet link após o delay
                    window.open(magnet, '_blank');
                    // Resetar após 2 segundos
                    setTimeout(() => {
                        this.done = false;
                        this.seconds = delaySeconds;
                    }, 2000);
                }
            }, 1000);
        }
    };
}
</script>
