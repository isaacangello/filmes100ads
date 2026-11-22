@extends('layouts.admin')

@section('page-title', 'Rendimentos')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">

    <div class="rounded-2xl border border-line bg-gradient-to-br from-ink-800 to-ink-700 p-6 shadow-card">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold">Rendimentos</h2>
                <p class="mt-1.5 max-w-xl text-sm text-muted">
                    Acompanhe os ganhos gerados pelas suas publicações — por visualizações.
                    Os valores são calculados com base no número de visualizações dos seus filmes.
                </p>
            </div>

            <span class="fda-badge">
                <i class="fas fa-chart-line text-[0.7rem] text-brand"></i> Dados reais
            </span>
        </div>
    </div>

    <livewire:earnings-dashboard />

</div>
@endsection
