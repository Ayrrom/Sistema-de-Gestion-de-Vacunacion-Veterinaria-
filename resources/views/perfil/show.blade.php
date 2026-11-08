@extends('layouts.app')

@section('title', 'Mi perfil - Veterinaria Uncaus')

@section('content')
    <div class="page-title">Mi perfil</div>
    <div class="page-subtitle">Tus datos y la forma en que estás usando el sistema</div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="page-card h-100">
                <h6 class="fw-bold mb-3">Datos</h6>
                <div class="dato-valor"><div class="dato-label">Nombre</div>{{ $user->name }}</div>
                <div class="dato-valor"><div class="dato-label">DNI</div>{{ $user->dni }}</div>
                <div class="dato-valor"><div class="dato-label">Rol</div>{{ $user->nombre_rol_real }}</div>

                @if ($user->veterinario)
                    <div class="dato-valor"><div class="dato-label">Cátedra</div>{{ $user->veterinario->catedra ?: '—' }}</div>
                    <div class="dato-valor">
                        <div class="dato-label">Estudiantes a cargo</div>
                        {{ $user->veterinario->estudiantes->pluck('nombre')->join(', ') ?: 'Ninguno' }}
                    </div>
                @elseif ($user->estudiante)
                    <div class="dato-valor"><div class="dato-label">Veterinario a cargo</div>{{ $user->estudiante->veterinario->nombre ?? '—' }}</div>
                @endif

                <a href="{{ route('password.edit') }}" class="btn btn-cancelar"><i class="bi bi-key"></i> Cambiar contraseña</a>
            </div>
        </div>

        @if ($user->puedeCambiarVista())
            <div class="col-md-6">
                <div class="page-card h-100">
                    <h6 class="fw-bold mb-3">Vista del sistema</h6>

                    <div class="dato-valor">
                        <div class="dato-label">Estás usando el sistema como</div>
                        <span class="badge {{ $user->enVistaEstudiante() ? 'text-bg-warning' : 'text-bg-primary' }} fs-6">{{ $user->nombre_rol }}</span>
                    </div>

                    <p class="small text-muted">
                        Podés ver el sistema como lo ve un estudiante: no vas a poder eliminar registros ni ver las
                        estadísticas hasta que vuelvas a tu rol. Tu rol de veterinario no cambia y al volver a
                        iniciar sesión entrás siempre como veterinario.
                    </p>

                    <form method="POST" action="{{ route('perfil.vista') }}">
                        @csrf
                        @if ($user->enVistaEstudiante())
                            <button type="submit" class="btn btn-guardar"><i class="bi bi-arrow-return-left"></i> Volver a Veterinario</button>
                        @else
                            <button type="submit" class="btn btn-cancelar"><i class="bi bi-mortarboard"></i> Ver como Estudiante</button>
                        @endif
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
