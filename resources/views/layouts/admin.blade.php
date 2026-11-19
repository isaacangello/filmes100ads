<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Área ADM — Filmes sem ADS')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-ink-950 text-chalk antialiased">

<div x-data="{ sidebar: false, userMenu: false }" class="min-h-screen lg:flex">

    {{-- ======================= SIDEBAR ======================= --}}
    <aside class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-line bg-ink-900 transition-transform lg:translate-x-0"
        :class="sidebar && '!translate-x-0'">

        <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-line px-5">
            <a href="{{ route('index') }}" class="flex items-center gap-2.5 font-extrabold tracking-tight">
                <span class="flex h-8.5 w-8.5 items-center justify-center rounded-[9px] bg-gradient-to-br from-brand to-orange-400 shadow-[0_6px_18px_rgb(229_9_20/0.3)]">
                    <i class="fas fa-film text-sm"></i>
                </span>
                <span>Filmes sem ADS</span>
            </a>
            <button @click="sidebar = false" class="ml-auto text-muted transition hover:text-white lg:hidden">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto p-4 text-sm">
            <div>
                <p class="px-3 pb-2 text-[0.68rem] font-bold tracking-[0.12em] text-faint uppercase">Conteúdo</p>
                <div class="space-y-1">
                    <a href="{{ route('adm') }}"
                        class="group relative flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition {{ request()->routeIs('adm') ? 'bg-brand-soft text-white' : 'text-muted hover:bg-ink-800 hover:text-white' }}">
                        @if (request()->routeIs('adm'))
                            <span class="absolute inset-y-2 left-0 w-0.5 rounded-full bg-brand"></span>
                        @endif
                        <i class="fas fa-gauge w-4 text-xs"></i> Painel
                    </a>

                    <a href="{{ route('cadastro.index') }}"
                        class="group relative flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition {{ request()->routeIs('cadastro.*') ? 'bg-brand-soft text-white' : 'text-muted hover:bg-ink-800 hover:text-white' }}">
                        @if (request()->routeIs('cadastro.*'))
                            <span class="absolute inset-y-2 left-0 w-0.5 rounded-full bg-brand"></span>
                        @endif
                        <i class="fas fa-file-circle-plus w-4 text-xs"></i> Cadastrar filme
                    </a>

                    <a href="{{ route('meuscadastros.index') }}"
                        class="group relative flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition {{ request()->routeIs('meuscadastros.*') ? 'bg-brand-soft text-white' : 'text-muted hover:bg-ink-800 hover:text-white' }}">
                        @if (request()->routeIs('meuscadastros.*'))
                            <span class="absolute inset-y-2 left-0 w-0.5 rounded-full bg-brand"></span>
                        @endif
                        <i class="fas fa-list w-4 text-xs"></i> Meus cadastros
                    </a>
                </div>
            </div>

            <div>
                <p class="px-3 pb-2 text-[0.68rem] font-bold tracking-[0.12em] text-faint uppercase">Site</p>
                <div class="space-y-1">
                    <a href="{{ route('torrents.index') }}"
                        class="group relative flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition {{ request()->routeIs('torrents.*') ? 'bg-brand-soft text-white' : 'text-muted hover:bg-ink-800 hover:text-white' }}">
                        @if (request()->routeIs('torrents.*'))
                            <span class="absolute inset-y-2 left-0 w-0.5 rounded-full bg-brand"></span>
                        @endif
                        <i class="fas fa-download w-4 text-xs"></i> Torrents
                    </a>

                    <a href="{{ route('adm.rendimentos') }}"
                        class="group relative flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition {{ request()->routeIs('adm.rendimentos') ? 'bg-brand-soft text-white' : 'text-muted hover:bg-ink-800 hover:text-white' }}">
                        @if (request()->routeIs('adm.rendimentos'))
                            <span class="absolute inset-y-2 left-0 w-0.5 rounded-full bg-brand"></span>
                        @endif
                        <i class="fas fa-coins w-4 text-xs"></i> Rendimentos
                    </a>

                    <a href="{{ route('index') }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium text-muted transition hover:bg-ink-800 hover:text-white">
                        <i class="fas fa-globe w-4 text-xs"></i> Ver site público
                    </a>
                </div>
            </div>
        </nav>

        <div class="shrink-0 border-t border-line p-4">
            <div class="flex items-center gap-3 rounded-xl bg-ink-850 px-3 py-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-soft text-[0.7rem] font-bold text-white">
                    {{ Str::upper(Str::substr(Auth::user()->name, 0, 2)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium">{{ Auth::user()->name }}</p>
                    <p class="truncate text-xs text-muted">{{ Auth::user()->email }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Sair" class="text-muted transition hover:text-brand">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden"></div>

    {{-- ======================= CONTEÚDO ======================= --}}
    <div class="flex min-h-screen flex-1 flex-col lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-4 border-b border-line bg-ink-950/85 px-4 backdrop-blur-xl sm:px-6">
            <button @click="sidebar = true" class="text-muted transition hover:text-white lg:hidden">
                <i class="fas fa-bars"></i>
            </button>

            <div class="min-w-0">
                <p class="text-[0.68rem] font-bold tracking-[0.12em] text-faint uppercase">Área ADM</p>
                <h1 class="truncate text-sm font-semibold">@yield('page-title', 'Painel')</h1>
            </div>

            <div class="ml-auto flex items-center gap-3">
                <a href="{{ route('cadastro.index') }}"
                    class="fda-btn fda-btn-primary hidden sm:inline-flex">
                    <i class="fas fa-plus text-xs"></i> Novo filme
                </a>

                <div class="relative">
                    <button @click="userMenu = !userMenu" @click.outside="userMenu = false"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-soft text-xs font-bold text-white transition hover:brightness-125">
                        {{ Str::upper(Str::substr(Auth::user()->name, 0, 2)) }}
                    </button>

                    <div x-show="userMenu" x-cloak x-transition.origin.top.right
                        class="absolute right-0 top-full z-50 mt-2 w-52 rounded-xl border border-line bg-ink-800 p-1.5 shadow-card">
                        <div class="border-b border-line px-3 py-2">
                            <p class="truncate text-sm font-medium">{{ Auth::user()->name }}</p>
                            <p class="truncate text-xs text-muted">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('index') }}" class="mt-1 block rounded-lg px-3 py-2 text-sm text-muted transition hover:bg-ink-700 hover:text-white">
                            <i class="fas fa-globe mr-2 text-xs"></i> Ver site
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-muted transition hover:bg-ink-700 hover:text-white">
                                <i class="fas fa-sign-out-alt mr-2 text-xs"></i> Sair
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>

        <footer class="border-t border-line px-6 py-4 text-xs text-muted">
            © {{ date('Y') }} Filmes sem ADS — painel administrativo · Laravel 11 · Livewire · Tailwind CSS
        </footer>
    </div>

</div>

@livewireScripts
</body>
</html>
