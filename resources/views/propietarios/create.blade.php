@extends('layouts.app')

@section('title', 'Registro de Propietario - Veterinaria Uncaus')

@section('content')
    <div class="page-title">Registro de Propietario</div>
    <div class="page-subtitle">Complete los datos del propietario</div>

    <div class="page-card" style="max-width: 720px;">
        <form method="POST" action="{{ route('propietarios.store') }}">
            @csrf
            @include('propietarios._form')
        </form>
    </div>
@endsection
