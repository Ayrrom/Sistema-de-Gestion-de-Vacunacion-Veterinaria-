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
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        <tr class="{{ $usuario->activo ? '' : 'equipo-inactivo' }}">
                            <td>{{ $usuario->dni }}</td>
                            <td>{{ $usuario->name }}</td>
                            <td>{{ $usuario->nombre_rol_real }}</td>
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
                            <td>
                                @if ($usuario->activo)
                                    <span class="badge text-bg-success"><i class="bi bi-check-circle"></i> Activo</span>
                                @else
                                    <span class="badge text-bg-secondary"><i class="bi bi-slash-circle"></i> Dado de baja</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                @if ($usuario->id !== Auth::id())
                                    @if ($usuario->activo)
                                        <form action="{{ route('equipo.baja', $usuario) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm({{ Js::from('¿Dar de baja a ' . $usuario->name . '? No va a poder ingresar al sistema, pero su historial se conserva.') }});">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-person-dash"></i> Dar de baja</button>
                                        </form>
                                    @else
                                        <form action="{{ route('equipo.alta', $usuario) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-person-check"></i> Dar de alta</button>
                                        </form>
                                    @endif
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
