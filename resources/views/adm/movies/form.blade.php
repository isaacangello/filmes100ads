@extends('layouts.admin')

@section('page-title', isset($post) ? 'Editar filme' : 'Cadastrar filme')

@section('content')
<div class="mx-auto max-w-4xl">
    <livewire:admin-movie-form :post="$post ?? null" />
</div>
@endsection
