@extends('layouts.app')

@section('title', $mascota->nombre . ' - Veterinaria Uncaus')

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
        .dato-label { font-size: 0.78rem; color: #8a90a2; margin-bottom: 0.1rem; }
        .dato-valor { margin-bottom: 1rem; }
    </style>

    <a href="{{ route('mascotas.index') }}" class="text-decoration-none d-inline-flex align-items-center gap-1 mb-3" style="font-size:0.9rem; font-weight:600; color:#1f2430;">
        <i class="bi bi-arrow-left"></i> Volver a la lista
    </a>

    <div class="page-title">Detalles del Paciente</div>
    <div class="page-subtitle">Información completa del paciente y su propietario</div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="page-card mb-3">
                <h6 class="fw-bold mb-3">Información del Paciente</h6>
                <div class="row">
                    <div class="col-md-6 dato-valor"><div class="dato-label">Nombre</div>{{ $mascota->nombre }}</div>
                    <div class="col-md-6 dato-valor"><div class="dato-label">Especie</div>{{ $mascota->especie }}</div>
                    <div class="col-md-6 dato-valor"><div class="dato-label">Raza</div>{{ $mascota->raza }}</div>
                    <div class="col-md-6 dato-valor"><div class="dato-label">Sexo</div>{{ $mascota->sexo }}</div>
                    <div class="col-md-6 dato-valor"><div class="dato-label">Edad</div>{{ $mascota->edad }} años</div>
                    <div class="col-md-6 dato-valor"><div class="dato-label">Peso</div>{{ $mascota->peso }} kg</div>
                    <div class="col-md-6 dato-valor"><div class="dato-label">Tipo de Pelaje</div>{{ $mascota->tipo_pelaje }}</div>
                    <div class="col-md-6 dato-valor"><div class="dato-label">Fecha de Nacimiento</div>{{ $mascota->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</div>
                    <div class="col-md-6 dato-valor"><div class="dato-label">Castrado</div>{{ $mascota->castrado ? 'Sí' : 'No' }}</div>
                    <div class="col-md-6 dato-valor"><div class="dato-label">Color</div>{{ $mascota->color ?: '—' }}</div>
                    @if ($mascota->procedencia)
                        <div class="col-12 dato-valor"><div class="dato-label">Procedencia</div>{{ $mascota->procedencia }}</div>
                    @endif
                    @if ($mascota->senas_particulares)
                        <div class="col-12 dato-valor"><div class="dato-label">Señas Particulares</div>{{ $mascota->senas_particulares }}</div>
                    @endif
                </div>
                <a href="{{ route('mascotas.edit', $mascota) }}" class="btn btn-cancelar">Editar datos</a>
            </div>

            <div class="page-card">
                <h6 class="fw-bold mb-3">Propietario</h6>
                <div class="row">
                    <div class="col-md-6 dato-valor"><div class="dato-label">Nombre</div>{{ $mascota->propietario->nombre }} {{ $mascota->propietario->apellido }}</div>
                    <div class="col-md-6 dato-valor"><div class="dato-label">DNI</div>{{ $mascota->propietario->dni }}</div>
                    <div class="col-md-6 dato-valor"><div class="dato-label">Teléfono</div>{{ $mascota->propietario->telefono }}</div>
                    <div class="col-md-6 dato-valor"><div class="dato-label">Localidad</div>{{ $mascota->propietario->localidad }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="page-card mb-3">
                <h6 class="fw-bold mb-3">Acciones</h6>
                <a href="{{ route('fichas.historial', $mascota) }}" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-file-earmark-text"></i> Ver Historia Clínica
                </a>
                <a href="{{ route('fichas.create', $mascota) }}" class="btn btn-success w-100">
                    <i class="bi bi-file-earmark-plus"></i> Añadir Ficha Clínica
                </a>
            </div>

            <div class="page-card">
                <h6 class="fw-bold mb-3">Última Visita</h6>
                @if ($ultimaFicha)
                    <div class="mb-2">
                        <div class="dato-label">Fecha</div>
                        <div>{{ $ultimaFicha->fecha_consulta->translatedFormat('d \d\e F, Y') }}</div>
                    </div>
                    <div>
                        <div class="dato-label">Estado</div>
                        @php
                            $badgeClass = match ($ultimaFicha->estado) {
                                'Saludable' => 'badge-saludable',
                                'En tratamiento' => 'badge-tratamiento',
                                'Revisión' => 'badge-revision',
                                default => 'badge-sin-consultas',
                            };
                        @endphp
                        <span class="badge-estado {{ $badgeClass }}">{{ $ultimaFicha->estado }}</span>
                    </div>
                @else
                    <p class="text-muted mb-0">Todavía no hay consultas registradas.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
