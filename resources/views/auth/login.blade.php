@extends('layouts.auth')

@section('title', 'Iniciar sesión - Veterinaria Uncaus')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="dni" class="form-label">DNI</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input
                    type="text"
                    class="form-control @error('dni') is-invalid @enderror"
                    id="dni"
                    name="dni"
                    placeholder="Ingrese su DNI"
                    value="{{ old('dni') }}"
                    autofocus
                >
            </div>
        </div>

        <div class="mb-2">
            <label for="password" class="form-label">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    placeholder="Ingrese su contraseña"
                >
            </div>
        </div>

        <div class="text-end mb-3">
            <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100">Ingresar</button>

        <div class="text-center mt-3" style="font-size: 0.85rem;">
            ¿No tenés cuenta? <a href="{{ route('register') }}">Registrate</a>
        </div>
    </form>
@endsection
