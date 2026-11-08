@extends('layouts.app')

@section('title', 'Cambiar contraseña - Veterinaria Uncaus')

@section('content')
    <div class="page-title">Cambiar contraseña</div>
    <div class="page-subtitle">La nueva contraseña tiene que tener entre 8 y 20 caracteres y no puede ser tu DNI</div>

    <div class="page-card" style="max-width: 480px;">
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="password_actual" class="form-label">Contraseña actual *</label>
                <input
                    type="password"
                    class="form-control @error('password_actual') is-invalid @enderror"
                    id="password_actual"
                    name="password_actual"
                    maxlength="20"
                    autocomplete="current-password"
                    autofocus
                >
                @error('password_actual')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if (Auth::user()->debe_cambiar_password)
                    <div class="form-text">Si es tu primer ingreso, tu contraseña actual es tu DNI.</div>
                @endif
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Nueva contraseña *</label>
                <input
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    maxlength="20"
                    autocomplete="new-password"
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Repetir nueva contraseña *</label>
                <input
                    type="password"
                    class="form-control"
                    id="password_confirmation"
                    name="password_confirmation"
                    maxlength="20"
                    autocomplete="new-password"
                >
            </div>

            <button type="submit" class="btn btn-guardar">Guardar contraseña</button>
        </form>
    </div>
@endsection
