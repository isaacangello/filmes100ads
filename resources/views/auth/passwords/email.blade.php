@extends('layouts.guest')

@section('title', 'Recuperar senha — Filmes sem ADS')

@section('content')
<div class="rounded-2xl border border-line bg-ink-800 p-8 shadow-card">
    <h1 class="text-2xl font-extrabold tracking-tight">Recuperar senha</h1>
    <p class="mt-1.5 text-sm text-muted">Informe seu e-mail e enviaremos as instruções para criar uma nova senha.</p>

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

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="fda-label">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                class="fda-input @error('email') border-red-500/60 @enderror">
            @error('email')
                <span class="mt-1 block text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="fda-btn fda-btn-primary w-full">Enviar instruções</button>
    </form>

    <div class="mt-6 text-center text-sm text-muted">
        <a href="{{ route('login') }}" class="font-semibold text-white transition hover:text-brand-hover">Voltar para o login</a>
    </div>
</div>
@endsection
