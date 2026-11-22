@extends('layouts.admin')

@section('title', 'Gerenciar usuários')
@section('page-title', 'Usuários')

@section('content')
<div class="mx-auto max-w-7xl">

    {{-- Mensagens de feedback --}}
    @if (session('success'))
        <div class="mb-4 rounded-xl border border-green-500/30 bg-green-500/10 p-3 text-sm text-green-400">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-400">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <livewire:user-manager />

</div>
@endsection
