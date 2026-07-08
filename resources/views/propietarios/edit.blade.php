@extends('layouts.app')

@section('title', 'Editar Propietario - Veterinaria Uncaus')

@section('content')
    <div class="page-title">Editar Propietario</div>
    <div class="page-subtitle">Modifique los datos del propietario</div>

    <div class="page-card" style="max-width: 720px;">
        <form method="POST" action="{{ route('propietarios.update', $propietario) }}">
            @csrf
            @method('PUT')
            @include('propietarios._form')
        </form>
    </div>
@endsection
