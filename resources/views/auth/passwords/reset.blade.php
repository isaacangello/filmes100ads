@extends('layouts.guest')

@section('title', 'Criar nova senha — Filmes sem ADS')

@section('content')
<div class="rounded-2xl border border-line bg-ink-800 p-8 shadow-card">
    <h1 class="text-2xl font-extrabold tracking-tight">Criar nova senha</h1>
    <p class="mt-1.5 text-sm text-muted">Escolha uma senha nova para acessar sua conta.</p>

    @if ($errors->any())
        <div class="mt-5 rounded-lg border border-red-500/40 bg-red-500/12 px-4 py-3 text-sm text-red-300">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="fda-label">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email ?? '') }}" required autocomplete="email" autofocus
                class="fda-input @error('email') border-red-500/60 @enderror">
            @error('email')
                <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password" class="fda-label">Nova senha</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="fda-input @error('password') border-red-500/60 @enderror">
            @error('password')
                <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password-confirm" class="fda-label">Confirme a nova senha</label>
            <input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password"
                class="fda-input">
        </div>

        <button type="submit" class="fda-btn fda-btn-primary w-full">Salvar nova senha</button>
    </form>
</div>
@endsection
