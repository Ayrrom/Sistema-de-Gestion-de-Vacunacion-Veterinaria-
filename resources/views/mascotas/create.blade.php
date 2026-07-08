@extends('layouts.app')

@section('title', 'Añadir Paciente - Veterinaria Uncaus')

@section('content')
    <div class="page-title">Añadir Nuevo Paciente</div>
    <div class="page-subtitle">Complete los datos del paciente</div>

    <div class="row g-3">
        <div class="col-md-4">
            @include('mascotas._propietario_card', ['propietario' => $propietario, 'buscarOtro' => true])
        </div>

        <div class="col-md-8">
            <div class="page-card">
                <form method="POST" action="{{ route('mascotas.store') }}">
                    @csrf
                    <input type="hidden" name="propietario_dni" value="{{ $propietario->dni }}">
                    @include('mascotas._form')
                </form>
            </div>
        </div>
    </div>
@endsection
