@extends('layouts.app')

@section('title', 'Equipo - Veterinaria Uncaus')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <div class="page-title">Equipo</div>
            <div class="page-subtitle mb-0">Alta, baja y contraseñas de estudiantes y veterinarios</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('equipo.create', ['rol' => 'estudiante']) }}" class="btn btn-guardar">
                <i class="bi bi-person-plus"></i> Nuevo estudiante
            </a>
            <a href="{{ route('equipo.create', ['rol' => 'veterinario']) }}" class="btn btn-cancelar">
                <i class="bi bi-person-plus"></i> Nuevo veterinario
            </a>
        </div>
    </div>

    <p class="small text-muted">
        Dar de baja no borra a la persona: deja de poder ingresar y de aparecer en los formularios, pero su historial
        (fichas que atendió, estudiantes a cargo) se conserva. Se la puede volver a dar de alta cuando quieras.
    </p>

    @foreach ([['Estudiantes', $estudiantes, 'estudiante'], ['Veterinarios', $veterinarios, 'veterinario']] as [$titulo, $miembros, $rol])
        <div class="page-card mb-3">
            <h6 class="fw-bold mb-3">
                {{ $titulo }}
                <span class="text-muted fw-normal small">· {{ $miembros->where('activo', true)->count() }} activos
                    @if ($miembros->where('activo', false)->count())
                        · {{ $miembros->where('activo', false)->count() }} dados de baja
                    @endif
                </span>
            </h6>

            @if ($miembros->isEmpty())
                <p class="text-muted small mb-0">Todavía no hay {{ strtolower($titulo) }} registrados.</p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>DNI</th>
                                <th>{{ $rol === 'estudiante' ? 'Veterinario a cargo' : 'Cátedra' }}</th>
                                @if ($rol === 'veterinario')
                                    <th>Estudiantes a cargo</th>
                                @endif
                                <th>Contraseña</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($miembros as $miembro)
                                <tr class="{{ $miembro->activo ? '' : 'equipo-inactivo' }}">
                                    <td class="fw-semibold">
                                        {{ $miembro->name }}
                                        @if ($miembro->id === Auth::id())
                                            <span class="badge text-bg-light border fw-normal">Vos</span>
                                        @endif
                                    </td>
                                    <td>{{ $miembro->dni }}</td>
                                    @if ($rol === 'estudiante')
                                        <td>{{ $miembro->estudiante?->veterinario?->nombre ?? '—' }}</td>
                                    @else
                                        <td>{{ $miembro->veterinario?->catedra ?: '—' }}</td>
                                        <td>{{ $miembro->veterinario?->estudiantes->count() ?? 0 }}</td>
                                    @endif
                                    <td>
                                        @if ($miembro->debe_cambiar_password)
                                            <span class="badge text-bg-warning">Inicial (DNI)</span>
                                        @else
                                            <span class="badge text-bg-light border">Personalizada</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($miembro->activo)
                                            <span class="badge text-bg-success"><i class="bi bi-check-circle"></i> Activo</span>
                                        @else
                                            <span class="badge text-bg-secondary"><i class="bi bi-slash-circle"></i> Dado de baja</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @if ($miembro->id !== Auth::id())
                                            <form method="POST" action="{{ route('equipo.restablecer-password', $miembro) }}" class="d-inline"
                                                  onsubmit="return confirm({{ Js::from('¿Restablecer la contraseña de ' . $miembro->name . ' a su DNI?') }});">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Restablecer contraseña al DNI">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restablecer contraseña
                                                </button>
                                            </form>
                                            @if ($miembro->activo)
                                                <form method="POST" action="{{ route('equipo.baja', $miembro) }}" class="d-inline"
                                                      onsubmit="return confirm({{ Js::from('¿Dar de baja a ' . $miembro->name . '? No va a poder ingresar al sistema, pero su historial se conserva.') }});">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-person-dash"></i> Dar de baja
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('equipo.alta', $miembro) }}" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-person-check"></i> Dar de alta
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endforeach
@endsection
