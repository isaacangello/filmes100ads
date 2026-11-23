@extends('layouts.admin')

@section('title', 'Minerador — Filmes sem ADS')
@section('page-title', 'Minerador CoinImp')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold">Dashboard do Minerador</h2>
            <p class="text-sm text-muted">Estatísticas e monitoramento do minerador CoinImp (MINT).</p>
        </div>
    </div>

    <livewire:miner-dashboard />
</div>
@endsection
