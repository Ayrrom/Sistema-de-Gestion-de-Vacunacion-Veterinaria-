@extends('layouts.app')

@section('title', 'Buscar Propietario - Veterinaria Uncaus')

@section('content')
    <div class="page-title">Añadir Nuevo Paciente</div>
    <div class="page-subtitle">Primero buscá al propietario por su DNI</div>

    <div class="page-card" style="max-width: 480px;">
        <form method="GET" action="{{ route('mascotas.create') }}">
            <label for="dni" class="form-label">DNI del propietario *</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input
                    type="text"
                    class="form-control"
                    id="dni"
                    name="dni"
                    placeholder="Ingrese el DNI"
                    autofocus
                >
            </div>
            <button type="submit" class="btn btn-guardar w-100 mt-3">Buscar</button>
        </form>

        <div class="text-center mt-3" style="font-size: 0.85rem;">
            ¿El propietario no está registrado?
            <a href="{{ route('propietarios.create') }}">Registrarlo primero</a>
        </div>
    </div>
@endsection
