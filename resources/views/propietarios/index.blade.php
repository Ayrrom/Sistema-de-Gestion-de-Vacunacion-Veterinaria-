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

    <div class="mb-3" style="max-width: 480px;">
        <form method="GET" action="{{ route('propietarios.index') }}">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input
                    type="search"
                    name="buscar"
                    class="form-control"
                    placeholder="Buscar por DNI, nombre o apellido..."
                    data-sugerencias="{{ route('propietarios.sugerencias') }}"
                    data-destino="{{ route('propietarios.show', '__DNI__') }}"
                    value="{{ $busqueda }}"
                    autofocus
                >
                <button type="submit" class="btn btn-guardar">Buscar</button>
            </div>
            <div class="form-text">Escribí el DNI y elegí al propietario de la lista, o escribilo completo y apretá Buscar.</div>
        </form>
    </div>

    <div class="page-card">
        @if ($propietarios->isEmpty())
            @if ($busqueda !== '')
                <p class="text-muted mb-2">No se encontró ningún propietario para "{{ $busqueda }}".</p>
                <a href="{{ route('propietarios.create') }}" class="btn btn-sm btn-guardar">
                    <i class="bi bi-plus-lg"></i> Registrar nuevo propietario
                </a>
                <a href="{{ route('propietarios.index') }}" class="btn btn-sm btn-cancelar">Ver todos</a>
            @else
                <p class="text-muted mb-0">Todavía no hay propietarios registrados.</p>
            @endif
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
                            <th>Veterinario</th>
                            <th>Mascotas</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($propietarios as $propietario)
                            <tr>
                                <td>{{ $propietario->dni }}</td>
                                <td><a href="{{ route('propietarios.show', $propietario) }}">{{ $propietario->nombre }}</a></td>
                                <td><a href="{{ route('propietarios.show', $propietario) }}">{{ $propietario->apellido }}</a></td>
                                <td>{{ $propietario->localidad }}</td>
                                <td>{{ $propietario->telefono }}</td>
                                <td>{{ $propietario->veterinario->nombre ?? '—' }}</td>
                                <td>{{ $propietario->mascotas_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('propietarios.show', $propietario) }}" class="btn btn-sm btn-outline-secondary" title="Ver perfil">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('propietarios.edit', $propietario) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if (Auth::user()->puedeEliminar())
                                        <form action="{{ route('propietarios.destroy', $propietario) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm({{ Js::from('¿Seguro que querés eliminar a ' . $propietario->nombre . ' ' . $propietario->apellido . '?') }});">
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

            {{ $propietarios->links() }}
        @endif
    </div>
@endsection
