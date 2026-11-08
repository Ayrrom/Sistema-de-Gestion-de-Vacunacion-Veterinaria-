@extends('layouts.app')

@section('title', 'Usuarios - Veterinaria Uncaus')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="page-title">Usuarios</div>
            <div class="page-subtitle mb-0">Personas que pueden ingresar al sistema</div>
        </div>
        <a href="{{ route('usuarios.create') }}" class="btn btn-guardar">
            <i class="bi bi-person-plus"></i> Nuevo usuario
        </a>
    </div>

    <div class="page-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Detalle</th>
                        <th>Contraseña</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->dni }}</td>
                            <td>{{ $usuario->name }}</td>
                            <td>{{ $usuario->nombre_rol }}</td>
                            <td>
                                @if ($usuario->veterinario)
                                    Cátedra: {{ $usuario->veterinario->catedra ?: '—' }}
                                @elseif ($usuario->estudiante)
                                    A cargo de: {{ $usuario->estudiante->veterinario->nombre ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($usuario->debe_cambiar_password)
                                    <span class="badge text-bg-warning">Inicial (DNI)</span>
                                @else
                                    <span class="badge text-bg-success">Personalizada</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($usuario->id !== Auth::id())
                                    <form action="{{ route('usuarios.restablecer-password', $usuario) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm({{ Js::from('¿Restablecer la contraseña de ' . $usuario->name . ' a su DNI?') }});">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restablecer contraseña
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
