@extends('layouts.app')

@section('title', 'Editar Consulta - Veterinaria Uncaus')

@section('content')
    <div class="page-title">Editar Ficha Clínica</div>
    <div class="page-subtitle">Paciente: {{ $mascota->nombre }}</div>

    <form method="POST" action="{{ route('fichas.update', $ficha) }}">
        @csrf
        @method('PUT')
        @include('fichas._form')
    </form>
@endsection
