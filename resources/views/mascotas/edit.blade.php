@extends('layouts.app')

@section('title', 'Editar Paciente - Veterinaria Uncaus')

@section('content')
    <div class="page-title">Editar Paciente</div>
    <div class="page-subtitle">Modifique los datos del paciente</div>

    <div class="row g-3">
        <div class="col-md-4">
            @include('mascotas._propietario_card', ['propietario' => $propietario, 'buscarOtro' => false])
        </div>

        <div class="col-md-8">
            <div class="page-card">
                <form method="POST" action="{{ route('mascotas.update', $mascota) }}">
                    @csrf
                    @method('PUT')
                    @include('mascotas._form')
                </form>
            </div>
        </div>
    </div>
@endsection
