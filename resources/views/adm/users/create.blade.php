@extends('layouts.admin')

@section('title', 'Criar usuário')
@section('page-title', 'Novo usuário')

@section('content')
<div class="mx-auto max-w-2xl">

    {{-- Mensagens de erro --}}
    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-400">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-line bg-ink-800 shadow-card">
        <div class="border-b border-line px-6 py-4">
            <h2 class="font-semibold">Criar novo usuário</h2>
            <p class="mt-1 text-xs text-muted">Preencha os dados para criar uma conta de usuário.</p>
        </div>

        <form action="{{ route('adm.users.store') }}" method="POST" class="p-6">
            @csrf

            {{-- Nome --}}
            <div class="mb-5">
                <label for="name" class="mb-1.5 block text-sm font-medium text-chalk">
                    Nome <span class="text-red-400">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="fda-input @error('name') border-red-500 @enderror"
                    placeholder="Nome completo do usuário">
                @error('name')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-5">
                <label for="email" class="mb-1.5 block text-sm font-medium text-chalk">
                    Email <span class="text-red-400">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="fda-input @error('email') border-red-500 @enderror"
                    placeholder="email@exemplo.com">
                @error('email')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Senha --}}
            <div class="mb-5">
                <label for="password" class="mb-1.5 block text-sm font-medium text-chalk">
                    Senha <span class="text-red-400">*</span>
                </label>
                <input type="password" name="password" id="password" required
                    class="fda-input @error('password') border-red-500 @enderror"
                    minlength="6">
                @error('password')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirmação de senha --}}
            <div class="mb-5">
                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-chalk">
                    Confirmar senha <span class="text-red-400">*</span>
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="fda-input" minlength="6">
            </div>

            {{-- É admin --}}
            <div class="mb-6">
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="is_admin" id="is_admin" value="1"
                        {{ old('is_admin') ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-line bg-ink-700 text-brand focus:ring-brand">
                    <span class="text-sm text-chalk">É administrador</span>
                </label>
                <p class="ml-7 mt-1 text-xs text-muted">
                    Marque se este usuário deve ter acesso total ao painel administrativo.
                </p>
            </div>

            {{-- Botões --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('adm.users.index') }}" class="fda-btn fda-btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="fda-btn fda-btn-primary">
                    <i class="fas fa-user-plus text-xs"></i> Criar usuário
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
