@extends('layouts.app')

@section('title', 'Inicio - Veterinaria Uncaus')

@section('content')

    <div class="page-title">Últimos Pacientes Atendidos</div>
    <div class="page-subtitle">Historial reciente de consultas veterinarias</div>

    <div class="mb-4" style="max-width: 420px;">
        <form method="GET" action="{{ route('dashboard') }}">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    placeholder="Buscar paciente..."
                    value="{{ $busqueda }}"
                    onchange="this.form.submit()"
                >
            </div>
        </form>
    </div>

    @if ($mascotas->isEmpty())
        <div class="page-card text-center text-muted">
            No se encontraron pacientes.
        </div>
    @else
        <div class="row g-3">
            @foreach ($mascotas as $mascota)
                @php
                    $badgeClass = match ($mascota->estado_actual) {
                        'Saludable' => 'badge-saludable',
                        'En tratamiento' => 'badge-tratamiento',
                        'Revisión' => 'badge-revision',
                        default => 'badge-sin-consultas',
                    };
                @endphp
                <div class="col-md-4">
                    <div class="paciente-card">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="paciente-nombre">{{ $mascota->nombre }}</span>
                            <span class="badge-estado {{ $badgeClass }}">{{ $mascota->estado_actual }}</span>
                        </div>
                        <div class="paciente-detalle mb-2">{{ $mascota->especie }} · {{ $mascota->raza }}</div>

                        <div class="paciente-detalle mb-1">
                            <i class="bi bi-person"></i>
                            {{ $mascota->propietario->nombre }} {{ $mascota->propietario->apellido }}
                        </div>
                        <div class="paciente-detalle mb-3">
                            <i class="bi bi-calendar3"></i>
                            @if ($mascota->ultima_visita)
                                Última visita: {{ $mascota->ultima_visita->format('d/m/Y') }}
                            @else
                                Sin visitas registradas
                            @endif
                        </div>

                        <a href="{{ route('mascotas.show', $mascota) }}" class="btn btn-ver-detalles w-100">Ver detalles</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">{{ $mascotas->links() }}</div>
    @endif
@endsection
