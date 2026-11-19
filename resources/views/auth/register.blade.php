@extends('layouts.guest')

@section('title', 'Criar conta — Filmes sem ADS')

@section('content')
<div class="rounded-2xl border border-line bg-ink-800 p-8 shadow-card">
    <h1 class="text-2xl font-extrabold tracking-tight">Criar conta</h1>
    <p class="mt-1.5 text-sm text-muted">Leva menos de um minuto e é grátis.</p>

    @if ($errors->any())
        <div class="mt-5 rounded-lg border border-red-500/40 bg-red-500/12 px-4 py-3 text-sm text-red-300">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="name" class="fda-label">Nome</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus
                class="fda-input @error('name') border-red-500/60 @enderror">
            @error('name')
                <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="email" class="fda-label">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                class="fda-input @error('email') border-red-500/60 @enderror">
            @error('email')
                <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password" class="fda-label">Senha</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="fda-input @error('password') border-red-500/60 @enderror">
            <small class="mt-1 block text-xs text-muted">Mínimo de 8 caracteres.</small>
            @error('password')
                <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password-confirm" class="fda-label">Confirme a senha</label>
            <input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password"
                class="fda-input">
        </div>

        <button type="submit" class="fda-btn fda-btn-primary w-full">Criar conta</button>
    </form>

    <div class="mt-6 text-center text-sm text-muted">
        Já tem conta?
        <a href="{{ route('login') }}" class="font-semibold text-white transition hover:text-brand-hover">Entrar</a>
    </div>
</div>
@endsection
