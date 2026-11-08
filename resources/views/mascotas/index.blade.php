@extends('layouts.app')

@section('title', 'Pacientes - Veterinaria Uncaus')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="page-title">Pacientes</div>
            <div class="page-subtitle mb-0">Listado de mascotas registradas</div>
        </div>
        <a href="{{ route('mascotas.create') }}" class="btn btn-guardar">
            <i class="bi bi-plus-lg"></i> Nuevo Paciente
        </a>
    </div>

    <div class="page-card mb-3">
        <form method="GET" action="{{ route('mascotas.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="filtro_dni" class="form-label small mb-1">DNI del propietario</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-person-vcard"></i></span>
                        <input type="text" class="form-control" id="filtro_dni" name="dni" value="{{ $filtros['dni'] }}"
                               placeholder="Ej: 3011" maxlength="8" inputmode="numeric" pattern="[0-9]*" title="Solo números" data-solo-numeros>
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="filtro_especie" class="form-label small mb-1">Especie</label>
                    <select class="form-select" id="filtro_especie" name="especie">
                        <option value="">Todas</option>
                        @foreach ($especies as $especie)
                            <option value="{{ $especie }}" {{ $filtros['especie'] === $especie ? 'selected' : '' }}>{{ $especie }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filtro_desde" class="form-label small mb-1">Consulta desde</label>
                    <input type="date" class="form-control" id="filtro_desde" name="desde" value="{{ $filtros['desde'] }}">
                </div>

                <div class="col-md-2">
                    <label for="filtro_hasta" class="form-label small mb-1">Consulta hasta</label>
                    <input type="date" class="form-control" id="filtro_hasta" name="hasta" value="{{ $filtros['hasta'] }}">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-guardar flex-grow-1"><i class="bi bi-funnel"></i> Filtrar</button>
                    @if ($hayFiltros)
                        <a href="{{ route('mascotas.index') }}" class="btn btn-cancelar">Limpiar</a>
                    @endif
                </div>
            </div>
            <div class="form-text">Para una fecha puntual, poné la misma en "desde" y "hasta".</div>
        </form>
    </div>

    <div class="page-card">
        @if ($hayFiltros)
            <p class="small text-muted mb-2">{{ $mascotas->total() }} {{ $mascotas->total() === 1 ? 'paciente encontrado' : 'pacientes encontrados' }}</p>
        @endif

        @if ($mascotas->isEmpty())
            @if ($hayFiltros)
                <p class="text-muted mb-0">No se encontraron pacientes con esos filtros.</p>
            @else
                <p class="text-muted mb-0">Todavía no hay pacientes registrados.</p>
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Raza</th>
                            <th>Sexo</th>
                            <th>Propietario</th>
                            <th>DNI Propietario</th>
                            <th>Última visita</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mascotas as $mascota)
                            <tr>
                                <td>{{ $mascota->nombre }}</td>
                                <td>{{ $mascota->especie }}</td>
                                <td>{{ $mascota->raza }}</td>
                                <td>{{ $mascota->sexo }}</td>
                                <td>{{ $mascota->propietario->nombre }} {{ $mascota->propietario->apellido }}</td>
                                <td>{{ $mascota->propietario_dni }}</td>
                                <td>{{ $mascota->ultimaFicha?->fecha_consulta->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('mascotas.show', $mascota) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('mascotas.edit', $mascota) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if (Auth::user()->puedeEliminar())
                                        <form action="{{ route('mascotas.destroy', $mascota) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm({{ Js::from('¿Seguro que querés eliminar a ' . $mascota->nombre . '?') }});">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $mascotas->links() }}
        @endif
    </div>
@endsection
