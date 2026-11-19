<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Filmes sem ADS')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen flex-col bg-ink-950 text-chalk antialiased">

    <header class="px-4 py-6 sm:px-6">
        <div class="mx-auto max-w-7xl">
            <a href="{{ route('index') }}" class="flex items-center gap-2.5 font-extrabold tracking-tight">
                <span class="flex h-8.5 w-8.5 items-center justify-center rounded-[9px] bg-gradient-to-br from-brand to-orange-400 shadow-[0_6px_18px_rgb(229_9_20/0.35)]">
                    <i class="fas fa-film text-sm"></i>
                </span>
                <span>Filmes sem ADS</span>
            </a>
        </div>
    </header>

    <main class="flex flex-1 items-center px-4 pb-14 sm:px-6">
        <div class="mx-auto w-full max-w-[460px]">
            @yield('content')
        </div>
    </main>

    <footer class="px-4 pb-8 text-center text-xs text-muted sm:px-6">
        © {{ date('Y') }} Filmes sem ADS ·
        <a href="{{ route('index') }}" class="font-medium transition hover:text-white">Voltar ao catálogo</a>
    </footer>

</body>
</html>
