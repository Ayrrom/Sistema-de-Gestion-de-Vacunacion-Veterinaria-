@extends('layouts.app')

@section('title', 'Nueva Consulta - Veterinaria Uncaus')

@section('content')
    <div class="page-title">Nueva Ficha Clínica</div>
    <div class="page-subtitle">Complete la información de la consulta para {{ $mascota->nombre }}</div>

    <form method="POST" action="{{ route('fichas.store', $mascota) }}">
        @csrf
        @include('fichas._form')
    </form>
@endsection
