@extends('layouts.app')

@section('title', 'Propietarios - Veterinaria Uncaus')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="page-title">Propietarios</div>
            <div class="page-subtitle mb-0">Listado de propietarios registrados</div>
        </div>
        <a href="{{ route('propietarios.create') }}" class="btn btn-guardar">
            <i class="bi bi-plus-lg"></i> Nuevo Propietario
        </a>
    </div>

    <div class="page-card">
        @if ($propietarios->isEmpty())
            <p class="text-muted mb-0">Todavía no hay propietarios registrados.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>DNI</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Localidad</th>
                            <th>Teléfono</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($propietarios as $propietario)
                            <tr>
                                <td>{{ $propietario->dni }}</td>
                                <td>{{ $propietario->nombre }}</td>
                                <td>{{ $propietario->apellido }}</td>
                                <td>{{ $propietario->localidad }}</td>
                                <td>{{ $propietario->telefono }}</td>
                                <td class="text-end">
                                    <a href="{{ route('propietarios.edit', $propietario) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('propietarios.destroy', $propietario) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que querés eliminar a {{ $propietario->nombre }} {{ $propietario->apellido }}?');">
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
