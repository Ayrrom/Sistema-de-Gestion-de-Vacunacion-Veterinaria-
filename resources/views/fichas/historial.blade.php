@extends('layouts.app')

@section('title', 'Historia Clínica - ' . $mascota->nombre)

@section('content')
    <style>
        .badge-estado {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 999px;
        }
        .badge-saludable { background-color: #e5f8ee; color: #16a34a; }
        .badge-tratamiento { background-color: #fef6e0; color: #b7791f; }
        .badge-revision { background-color: #eaf0fe; color: #2f6fed; }
        .badge-sin-consultas { background-color: #f0f1f5; color: #8a90a2; }
    </style>

    <a href="{{ route('mascotas.show', $mascota) }}" class="text-decoration-none d-inline-flex align-items-center gap-1 mb-3" style="font-size:0.9rem; font-weight:600; color:#1f2430;">
        <i class="bi bi-arrow-left"></i> Volver a detalles
    </a>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <div class="page-title">Historia Clínica</div>
            <div class="page-subtitle mb-0">Paciente: {{ $mascota->nombre }} ({{ $mascota->especie }} · {{ $mascota->raza }})</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('fichas.historial.pdf', $mascota) }}" class="btn btn-cancelar">
                <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
            </a>
            <a href="{{ route('fichas.create', $mascota) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-plus"></i> Añadir Ficha Clínica
            </a>
        </div>
    </div>

    <div class="page-card">
        @if ($fichas->isEmpty())
            <p class="text-muted mb-0">Todavía no hay consultas registradas para este paciente.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Veterinario</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fichas as $ficha)
                            @php
                                $badgeClass = match ($ficha->estado) {
                                    'Saludable' => 'badge-saludable',
                                    'En tratamiento' => 'badge-tratamiento',
                                    'Revisión' => 'badge-revision',
                                    default => 'badge-sin-consultas',
                                };
                            @endphp
                            <tr>
                                <td>{{ $ficha->fecha_consulta->format('d/m/Y') }}</td>
                                <td>{{ $ficha->veterinario ?: '—' }}</td>
                                <td>{{ $ficha->motivo_consulta }}</td>
                                <td><span class="badge-estado {{ $badgeClass }}">{{ $ficha->estado }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('fichas.show', $ficha) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('fichas.edit', $ficha) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('fichas.destroy', $ficha) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que querés eliminar esta consulta?');">
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
