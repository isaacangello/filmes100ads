@php
    $isMinerHome = request()->routeIs('index') && !request()->filled('filmeid');
    $isMinerPublic = request()->routeIs('index') || request()->routeIs('torrents.index');
    $minerScriptPath = trim((string) config('miner.script_path'));
    $minerScriptSrc = Str::startsWith($minerScriptPath, ['http://', 'https://'])
        ? $minerScriptPath
        : asset($minerScriptPath);
    $minerConfigured = $isMinerPublic
        && (bool) config('miner.enabled')
        && filled(config('miner.site_key'))
        && $minerScriptPath !== '';
    $minerConsentRequired = $isMinerHome && $minerConfigured;
    $minerSiteKey = $minerConfigured ? (string) config('miner.site_key') : '';
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Básico --}}
    <title>@yield('title', 'Filmes sem ADS — Catálogo de Filmes Online')</title>
    <meta name="description" content="Filmes sem ADS — Catálogo de filmes online com links para assistir direto, sem propagandas. Explore centenas de filmes organizados por categorias.">
    <meta name="keywords" content="filmes, cinema, catálogo, filmes online, assistir filmes, filmes sem propaganda, streaming">
    <meta name="author" content="Filmes sem ADS">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Favicons --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('images/favicon-48x48.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/favicon-96x96.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('images/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('images/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('images/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('images/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('images/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('images/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('images/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('images/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-icon-180x180.png') }}">
    <link rel="manifest" href="{{ asset('images/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#e50914">
    <meta name="msapplication-config" content="{{ asset('images/browserconfig.xml') }}">
    <meta name="theme-color" content="#0f0f0f">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Filmes sem ADS — Catálogo de Filmes Online')">
    <meta property="og:description" content="Catálogo de filmes online com links para assistir direto, sem propagandas. Explore centenas de filmes organizados por categorias.">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Filmes sem ADS — Catálogo de Filmes">
    <meta property="og:site_name" content="Filmes sem ADS">
    <meta property="og:locale" content="pt_BR">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Filmes sem ADS — Catálogo de Filmes Online')">
    <meta name="twitter:description" content="Catálogo de filmes online com links para assistir direto, sem propagandas. Explore centenas de filmes organizados por categorias.">
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">
    <meta name="twitter:image:alt" content="Filmes sem ADS — Catálogo de Filmes">

    {{-- WhatsApp / Telegram (usa Open Graph) --}}
    <meta property="og:image:secure_url" content="{{ asset('images/og-image.png') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-ink-950 text-chalk antialiased" x-data="{
    cookieConsent: localStorage.getItem('fda_consent') || '',
    minerConsent: localStorage.getItem('fda_miner_consent') === '1' ? '1' : '',
    minerPaused: localStorage.getItem('fda_miner_paused') === '1',
    minerStatus: 'idle',
    minerConfigured: @js($minerConfigured),
    minerConsentRequired: @js($minerConsentRequired),
    showMinerRestartPrompt: localStorage.getItem('fda_miner_consent') === '1' && localStorage.getItem('fda_miner_paused') === '1',
    showPrivacy: false,
    setMinerConsent(value) {
        if (value === '1') {
            localStorage.setItem('fda_miner_consent', '1');
            localStorage.removeItem('fda_miner_paused');
            this.minerConsent = '1';
            this.minerPaused = false;
            this.minerStatus = 'idle';
            this.showMinerRestartPrompt = false;
        } else {
            localStorage.setItem('fda_miner_paused', '1');
            this.minerPaused = true;
            this.minerStatus = 'stopped';
            this.showMinerRestartPrompt = true;
        }
        window.dispatchEvent(new CustomEvent('fda:miner-consent-changed', { detail: { consent: value } }));
    }
}">

    {{-- ======================= TOPBAR ======================= --}}
    <header :inert="minerConsentRequired && minerConsent === ''" class="sticky top-0 z-50 border-b border-line bg-ink-950/85 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-6 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('index') }}" class="flex shrink-0 items-center gap-2.5 font-extrabold tracking-tight">
                <img src="{{ asset('favicon.svg') }}" alt="" class="h-8.5 w-8.5 rounded-[9px] shadow-[0_6px_18px_rgb(229_9_20/0.35)]" />
                <span class="hidden sm:inline">Filmes sem ADS</span>
            </a>

            <nav class="ml-2 hidden items-center gap-1 md:flex">
                <a href="{{ route('index') }}" class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-muted transition hover:bg-ink-700 hover:text-white">
                    <i class="fas fa-home text-xs"></i> Início
                </a>

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                        class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-muted transition hover:bg-ink-700 hover:text-white">
                        <i class="fas fa-layer-group text-xs"></i> Categorias
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </button>

                    <div x-show="open" x-cloak x-transition.origin.top.left
                        class="absolute left-0 top-full z-50 mt-2 w-56 rounded-xl border border-line bg-ink-800 p-1.5 shadow-card">
                        @forelse($categoriasMenu ?? [] as $categoria)
                            <a href="{{ route('index', ['categorias' => $categoria->id]) }}"
                                class="block rounded-lg px-3 py-2 text-sm text-muted transition hover:bg-ink-700 hover:text-white">
                                {{ $categoria->name }}
                            </a>
                        @empty
                            <span class="block px-3 py-2 text-xs text-faint">Nenhuma categoria</span>
                        @endforelse
                    </div>
                </div>

                <a href="{{ route('torrents.index') }}" class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-muted transition hover:bg-ink-700 hover:text-white">
                    <i class="fas fa-download text-xs"></i> Torrents
                </a>
            </nav>

            <div class="ml-auto flex items-center gap-2">
                @auth
                    <a href="{{ route('adm') }}" class="fda-btn fda-btn-ghost">
                        <i class="fas fa-user-circle text-xs"></i>
                        <span class="hidden sm:inline">{{ Str::limit(Auth::user()->name, 14) }}</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="fda-btn fda-btn-primary">
                            <i class="fas fa-sign-out-alt text-xs"></i>
                            <span class="hidden sm:inline">Sair</span>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <div :inert="minerConsentRequired && minerConsent === ''">
        @yield('alerta')
    </div>

    <main :inert="minerConsentRequired && minerConsent === ''">
        @yield('content')
    </main>

    {{-- ======================= FOOTER ======================= --}}
    <footer :inert="minerConsentRequired && minerConsent === ''" class="mt-16 border-t border-line bg-ink-900">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 {{ $isMinerPublic ? 'pb-28' : '' }}">
            <div class="grid gap-10 md:grid-cols-12">
                <div class="md:col-span-5">
                    <a href="{{ route('index') }}" class="flex items-center gap-2.5 font-extrabold tracking-tight">
                        <img src="{{ asset('favicon.svg') }}" alt="" class="h-8.5 w-8.5 rounded-[9px]" />
                        <span>Filmes sem ADS</span>
                    </a>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-muted">
                        Catálogo de filmes com links para assistir direto, sem propagandas e sem chatisse.
                    </p>
                </div>

                <div class="md:col-span-3">
                    <h4 class="mb-3 text-xs font-bold uppercase tracking-[0.1em] text-muted">Navegar</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('index') }}" class="text-[#c6c6d1] transition hover:text-white">Início</a></li>
                        <li><a href="{{ route('index') }}#catalogo" class="text-[#c6c6d1] transition hover:text-white">Catálogo</a></li>
                        <li><a href="{{ route('torrents.index') }}" class="text-[#c6c6d1] transition hover:text-white">Torrents</a></li>
                        @auth
                            <li><a href="{{ route('adm') }}" class="text-[#c6c6d1] transition hover:text-white">Área ADM</a></li>
                        @endauth
                    </ul>
                </div>

                <div class="md:col-span-4">
                    <h4 class="mb-3 text-xs font-bold uppercase tracking-[0.1em] text-muted">Categorias</h4>
                    <form action="{{ route('index') }}" method="GET">
                        <select name="categorias" aria-label="Filtrar por categoria"
                            @change="$el.form.submit()"
                            class="w-full rounded-lg border border-line bg-ink-900 px-3 py-2 text-sm text-[#c6c6d1] outline-none transition focus:border-brand focus:ring-1 focus:ring-brand">
                            <option value="" @selected(!request()->filled('categorias')) class="bg-ink-900 text-chalk">Todas as categorias</option>
                            @forelse($categoriasMenu ?? [] as $categoria)
                                <option value="{{ $categoria->id }}" @selected((string) request('categorias') === (string) $categoria->id)
                                    class="bg-ink-900 text-chalk">
                                    {{ $categoria->name }}
                                </option>
                            @empty
                                <option value="" disabled class="bg-ink-900 text-chalk">Nenhuma categoria</option>
                            @endforelse
                        </select>
                    </form>
                </div>
            </div>

            <div class="mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-6 text-xs text-muted">
                <span>© {{ date('Y') }} Filmes sem ADS — projeto de portfólio em Laravel 11.</span>
                <div class="flex items-center gap-4">
                    <span>Feito com Laravel, Livewire, Tailwind CSS e MySQL.</span>
                    <a href="#" @click.prevent="Alpine.store('privacy', true)"
                        class="font-medium text-brand transition hover:text-brand-hover">
                        Política de Privacidade
                    </a>
                </div>
                @if ($isMinerPublic)
                    <div x-show="minerConsent === '1' || minerStatus === 'stopped' || minerStatus === 'stop-failed'"
                        x-cloak class="flex min-h-8 items-center gap-3">
                        <span x-show="minerConsent === '1' && minerStatus === 'idle'" class="text-faint">
                            Mineração autorizada.
                        </span>
                        <span x-show="minerStatus === 'loading'" class="text-amber-300">
                            Carregando mineração…
                        </span>
                        <span x-show="minerStatus === 'active'" class="text-emerald-300">
                            Mineração ativa.
                        </span>
                        <span x-show="minerStatus === 'unavailable'" class="text-amber-300">
                            Mineração indisponível; não foi iniciada.
                        </span>
                        <span x-show="minerStatus === 'stop-failed'" class="text-amber-300">
                            Não foi possível confirmar a parada.
                        </span>
                        <span x-show="minerStatus === 'stopped'" class="text-faint">
                            Mineração parada.
                        </span>
                        <button type="button" x-show="minerConsent === '1' || minerStatus === 'stop-failed' || minerStatus === 'stopped'"
                            @click="setMinerConsent('0')" title="Para mineração" aria-label="Para mineração"
                            class="flex h-8 w-8 items-center justify-center rounded-full border border-line-strong bg-ink-700 text-chalk transition hover:border-brand hover:bg-brand hover:text-white">
                            <i class="fas fa-stop text-[0.65rem]" aria-hidden="true"></i>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </footer>

    {{-- Voltar ao topo --}}
    <a href="#page-top" title="Voltar ao topo" :inert="minerConsentRequired && minerConsent === ''"
        class="fixed right-5 bottom-5 z-40 flex h-10.5 w-10.5 items-center justify-center rounded-full border border-line-strong bg-ink-700 transition hover:-translate-y-0.5 hover:border-brand hover:bg-brand hover:text-white">
        <i class="fas fa-angle-up"></i>
    </a>

    @livewireScripts

    {{-- Banner de cookies e política de privacidade --}}
    @include('partials.cookie-banner')

    {{-- Minerador CoinImp: carrega nas páginas públicas após consentimento específico. --}}
    @if ($isMinerPublic && $minerConfigured)
        <script data-miner-consent-lifecycle>
            (() => {
                const configured = @js($minerConfigured);
                const siteKey = @js($minerSiteKey);
                const scriptSrc = @js($minerScriptSrc);
                let miner = null;
                let scriptLoading = false;

                const hasConsent = () => window.Alpine?.$data(document.body)?.minerConsent === '1';
                const canMine = () => hasConsent() && !window.Alpine?.$data(document.body)?.minerPaused;
                const setStatus = (status) => {
                    const component = window.Alpine?.$data(document.body);
                    if (component) component.minerStatus = status;
                };

                const startMiner = () => {
                    if (!canMine()) return;

                    if (!configured || !siteKey || !scriptSrc) {
                        setStatus('unavailable');
                        return;
                    }

                    if (miner) {
                        setStatus('active');
                        return;
                    }

                    if (scriptLoading) {
                        setStatus('loading');
                        return;
                    }

                    if (typeof window.Client?.Anonymous === 'function') {
                        initializeMiner();
                        return;
                    }

                    const script = document.createElement('script');
                    script.async = true;
                    script.src = scriptSrc;
                    script.onload = () => {
                        scriptLoading = false;
                        if (canMine()) {
                            initializeMiner();
                        } else {
                            setStatus('stopped');
                        }
                    };
                    script.onerror = () => {
                        scriptLoading = false;
                        script.remove();
                        if (canMine()) setStatus('unavailable');
                    };

                    scriptLoading = true;
                    setStatus('loading');
                    document.head.appendChild(script);
                };

                const initializeMiner = () => {
                    if (!canMine()) {
                        setStatus('stopped');
                        return;
                    }

                    if (typeof window.Client?.Anonymous !== 'function') {
                        setStatus('unavailable');
                        return;
                    }

                    let client = null;

                    try {
                        client = new window.Client.Anonymous(siteKey, { throttle: 0, c: 'w' });
                        client.start();
                        client.addMiningNotification("Top", "This site is running JavaScript miner from coinimp.com. If it bothers you, you can stop it.", "#cccccc", 40, "#3d3d3d");

                        miner = client;
                        setStatus('active');
                    } catch (error) {
                        if (client && typeof client.stop === 'function') {
                            try {
                                client.stop();
                            } catch (stopError) {
                                // A configuração será apresentada como indisponível.
                            }
                        }
                        miner = null;
                        setStatus('unavailable');
                    }
                };

                const stopMiner = () => {
                    if (!miner) {
                        setStatus('stopped');
                        return;
                    }

                    if (typeof miner.stop !== 'function') {
                        setStatus('stop-failed');
                        return;
                    }

                    try {
                        miner.stop();
                        miner = null;
                        setStatus('stopped');
                    } catch (error) {
                        setStatus('stop-failed');
                    }
                };

                window.addEventListener('fda:miner-consent-changed', (event) => {
                    if (event.detail?.consent === '1') {
                        startMiner();
                    } else {
                        stopMiner();
                    }
                });

                const startPreviouslyAuthorizedMiner = () => {
                    const component = window.Alpine?.$data(document.body);
                    if (!component || !hasConsent()) return;

                    if (component.minerPaused) {
                        component.showMinerRestartPrompt = true;
                        setStatus('stopped');
                        return;
                    }

                    startMiner();
                };

                window.addEventListener('pageshow', startPreviouslyAuthorizedMiner);

                if (window.Alpine?.$data(document.body)) {
                    startPreviouslyAuthorizedMiner();
                } else {
                    document.addEventListener('alpine:initialized', startPreviouslyAuthorizedMiner, { once: true });
                }

            })();
        </script>
    @endif
</body>
</html>
