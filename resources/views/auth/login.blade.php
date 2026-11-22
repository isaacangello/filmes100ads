@extends('layouts.guest')

@section('title', 'Entrar — Filmes sem ADS')

@section('content')
<div class="rounded-2xl border border-line bg-ink-800 p-8 shadow-card">
    <h1 class="text-2xl font-extrabold tracking-tight">Entrar</h1>
    <p class="mt-1.5 text-sm text-muted">Acesse sua conta para gerenciar os filmes do seu perfil.</p>

    @if (session('status'))
        <div class="mt-5 rounded-lg border border-emerald-500/35 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-5 rounded-lg border border-red-500/40 bg-red-500/12 px-4 py-3 text-sm text-red-300">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="fda-label">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                class="fda-input @error('email') border-red-500/60 @enderror">
            @error('email')
                <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password" class="fda-label">Senha</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="fda-input @error('password') border-red-500/60 @enderror">
            @error('password')
                <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label for="remember" class="flex cursor-pointer items-center gap-2 text-sm text-muted">
                <input type="checkbox" name="remember" id="remember" value="1"
                    class="h-4 w-4 rounded border-line-strong bg-ink-700 text-brand focus:ring-brand"
                    @checked(old('remember'))>
                Continuar conectado
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-muted transition hover:text-white">Esqueci a senha</a>
            @endif
        </div>

        <button type="submit" class="fda-btn fda-btn-primary w-full">Entrar</button>
    </form>

</div>
@endsection
