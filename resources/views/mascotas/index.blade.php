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

    <div class="page-card">
        @if ($mascotas->isEmpty())
            <p class="text-muted mb-0">Todavía no hay pacientes registrados.</p>
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
                                <td class="text-end">
                                    <a href="{{ route('mascotas.show', $mascota) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('mascotas.edit', $mascota) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('mascotas.destroy', $mascota) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que querés eliminar a {{ $mascota->nombre }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
