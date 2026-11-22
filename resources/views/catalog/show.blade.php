@extends('layouts.public')

@section('title', ($filmeNome ?? 'Filme') . ' — Filmes sem ADS')

@section('content')
    <livewire:movie-detail :filmeid="$filmeid" />
@endsection
