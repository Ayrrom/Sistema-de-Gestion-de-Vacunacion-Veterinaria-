@extends('layouts.auth')

@section('title', 'Registrarse - Veterinaria Uncaus')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    id="name"
                    name="name"
                    placeholder="Ingrese su nombre"
                    value="{{ old('name') }}"
                    autofocus
                >
            </div>
        </div>

        <div class="mb-3">
            <label for="dni" class="form-label">DNI</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                <input
                    type="text"
                    class="form-control @error('dni') is-invalid @enderror"
                    id="dni"
                    name="dni"
                    placeholder="Ingrese su DNI"
                    value="{{ old('dni') }}"
                >
            </div>
        </div>

        <div class="alert alert-info py-2 small">
            Tu contraseña inicial va a ser tu mismo número de DNI.
        </div>

        <button type="submit" class="btn btn-primary w-100">Registrarse</button>

        <div class="text-center mt-3" style="font-size: 0.85rem;">
            ¿Ya tenés cuenta? <a href="{{ route('login') }}">Iniciar sesión</a>
        </div>
    </form>
@endsection
