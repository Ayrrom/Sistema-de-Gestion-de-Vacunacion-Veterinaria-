@extends('layouts.app')

@section('title', 'Inicio - Veterinaria Uncaus')

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

        .paciente-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1.25rem;
            height: 100%;
        }

        .paciente-nombre {
            font-weight: 700;
            font-size: 1.05rem;
            color: #1f2430;
        }

        .paciente-detalle {
            font-size: 0.82rem;
            color: #8a90a2;
        }

        .btn-ver-detalles {
            background-color: #eaf0fe;
            color: #2f6fed;
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 8px;
            padding: 0.45rem 0;
        }

        .btn-ver-detalles:hover {
            background-color: #d7e3fd;
            color: #1f4fc4;
        }
    </style>

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

                        <a href="{{ route('mascotas.edit', $mascota) }}" class="btn btn-ver-detalles w-100">Ver detalles</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
