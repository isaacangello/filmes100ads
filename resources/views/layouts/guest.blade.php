<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    <link rel="icon" type="image/png" href="{{ asset('images/logo-filmes100ads.png') }}">
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
</head>

<body class="flex min-h-screen flex-col bg-ink-950 text-chalk antialiased">

    <header class="px-4 py-6 sm:px-6">
        <div class="mx-auto max-w-7xl">
            <a href="{{ route('index') }}" class="flex items-center gap-2.5 font-extrabold tracking-tight">
                <img src="{{ asset('images/logo-filmes100ads.png') }}" alt="" class="h-8.5 w-8.5 rounded-[9px] shadow-[0_6px_18px_rgb(229_9_20/0.35)]" />
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
