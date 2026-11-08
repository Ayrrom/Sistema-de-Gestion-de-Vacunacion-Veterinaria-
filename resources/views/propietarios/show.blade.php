@extends('layouts.app')

@section('title', $propietario->nombre . ' ' . $propietario->apellido . ' - Veterinaria Uncaus')

@section('content')
    <a href="{{ route('propietarios.index') }}" class="text-decoration-none d-inline-flex align-items-center gap-1 mb-3" style="font-size:0.9rem; font-weight:600; color:#1f2430;">
        <i class="bi bi-arrow-left"></i> Volver a propietarios
    </a>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <div class="page-title">{{ $propietario->nombre }} {{ $propietario->apellido }}</div>
            <div class="page-subtitle mb-0">DNI {{ $propietario->dni }}</div>
        </div>
        <a href="{{ route('propietarios.edit', $propietario) }}" class="btn btn-cancelar">
            <i class="bi bi-pencil"></i> Editar datos
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="page-card">
                <h6 class="fw-bold mb-3">Datos del Propietario</h6>
                <div class="dato-valor"><div class="dato-label">Teléfono</div>{{ $propietario->telefono }}</div>
                <div class="dato-valor"><div class="dato-label">Dirección</div>{{ $propietario->calle }} {{ $propietario->altura }}</div>
                <div class="dato-valor"><div class="dato-label">Localidad</div>{{ $propietario->localidad }}</div>
                <div class="dato-valor mb-0"><div class="dato-label">Veterinario que lo atiende</div>{{ $propietario->veterinario->nombre ?? '—' }}</div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="page-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Mascotas ({{ $propietario->mascotas->count() }})</h6>
                    <a href="{{ route('mascotas.create', ['dni' => $propietario->dni]) }}" class="btn btn-sm btn-guardar">
                        <i class="bi bi-plus-lg"></i> Añadir mascota
                    </a>
                </div>

                @if ($propietario->mascotas->isEmpty())
                    <p class="text-muted mb-0">Este propietario todavía no tiene mascotas registradas.</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Especie / Raza</th>
                                    <th>Última visita</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($propietario->mascotas as $mascota)
                                    @php
                                        $ultimaFicha = $mascota->ultimaFicha;
                                        $estado = $ultimaFicha->estado ?? 'Sin consultas';
                                        $badgeClass = match ($estado) {
                                            'Saludable' => 'badge-saludable',
                                            'En tratamiento' => 'badge-tratamiento',
                                            'Revisión' => 'badge-revision',
                                            default => 'badge-sin-consultas',
                                        };
                                    @endphp
                                    <tr>
                                        <td><a href="{{ route('mascotas.show', $mascota) }}" class="fw-semibold">{{ $mascota->nombre }}</a></td>
                                        <td>{{ $mascota->especie }} · {{ $mascota->raza }}</td>
                                        <td>{{ $ultimaFicha?->fecha_consulta->format('d/m/Y') ?? '—' }}</td>
                                        <td><span class="badge-estado {{ $badgeClass }}">{{ $estado }}</span></td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('mascotas.show', $mascota) }}" class="btn btn-sm btn-outline-secondary" title="Ver paciente">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('fichas.historial', $mascota) }}" class="btn btn-sm btn-outline-primary" title="Historia clínica">
                                                <i class="bi bi-file-earmark-text"></i>
                                            </a>
                                            <a href="{{ route('fichas.create', $mascota) }}" class="btn btn-sm btn-outline-success" title="Nueva consulta">
                                                <i class="bi bi-file-earmark-plus"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
