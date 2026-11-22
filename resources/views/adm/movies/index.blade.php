@extends('layouts.admin')

@section('page-title', 'Meus cadastros')

@section('content')
<div class="mx-auto max-w-6xl">
    <livewire:admin-movie-list />
</div>
@endsection
