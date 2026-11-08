@extends('layouts.app')

@section('title', 'Nuevo usuario - Veterinaria Uncaus')

@section('content')
    <div class="page-title">Nuevo usuario</div>
    <div class="page-subtitle">Dar de alta a una persona para que pueda ingresar al sistema</div>

    <div class="page-card" style="max-width: 720px;">
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nombre *</label>
                    <input
                        type="text"
                        class="form-control @error('name') is-invalid @enderror"
                        id="name"
                        name="name"
                        placeholder="Ingrese el nombre"
                        value="{{ old('name') }}"
                        autofocus
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="dni" class="form-label">DNI *</label>
                    <input
                        type="text"
                        class="form-control @error('dni') is-invalid @enderror"
                        id="dni"
                        name="dni"
                        placeholder="Ingrese el DNI"
                        value="{{ old('dni') }}"
                    >
                    @error('dni')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="alert alert-info py-2 small mt-3">
                La contraseña inicial va a ser el número de DNI. El usuario va a tener que cambiarla la primera vez que ingrese.
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-guardar">Crear usuario</button>
                <a href="{{ route('dashboard') }}" class="btn btn-cancelar">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
