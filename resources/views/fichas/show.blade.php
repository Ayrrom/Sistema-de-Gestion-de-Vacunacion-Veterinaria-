@extends('layouts.app')

@section('title', 'Detalle de Consulta - Veterinaria Uncaus')

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
        .dato-label { font-size: 0.78rem; color: #8a90a2; margin-bottom: 0.1rem; }
        .dato-valor { margin-bottom: 1rem; }
    </style>

    @php
        $badgeClass = match ($ficha->estado) {
            'Saludable' => 'badge-saludable',
            'En tratamiento' => 'badge-tratamiento',
            'Revisión' => 'badge-revision',
            default => 'badge-sin-consultas',
        };

        $examenItems = [
            'piel_anexos' => 'Piel y anexos',
            'ojos' => 'Ojos',
            'oidos' => 'Oídos',
            'ganglios_linfaticos' => 'Ganglios linfáticos',
            'conjuntivas_mucosas' => 'Conjuntivas-mucosas',
            'sistema_cardiaco' => 'Sistema cardíaco',
            'sistema_respiratorio' => 'Sistema respiratorio',
            'sistema_digestivo' => 'Sistema digestivo',
            'sistema_urinario' => 'Sistema urinario',
            'sistema_nervioso' => 'Sistema nervioso',
            'sistema_locomotor' => 'Sistema locomotor',
        ];

        $condiciones = [1 => 'Caquéctico', 2 => 'Delgado', 3 => 'Ideal', 4 => 'Sobrepeso', 5 => 'Obeso'];
    @endphp

    <a href="{{ route('fichas.historial', $mascota) }}" class="text-decoration-none d-inline-flex align-items-center gap-1 mb-3" style="font-size:0.9rem; font-weight:600; color:#1f2430;">
        <i class="bi bi-arrow-left"></i> Volver al historial
    </a>

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <div class="page-title">Consulta del {{ $ficha->fecha_consulta->format('d/m/Y') }}</div>
            <div class="page-subtitle mb-0">Paciente: {{ $mascota->nombre }} · <span class="badge-estado {{ $badgeClass }}">{{ $ficha->estado }}</span></div>
        </div>
        <a href="{{ route('fichas.edit', $ficha) }}" class="btn btn-cancelar"><i class="bi bi-pencil"></i> Editar</a>
    </div>

    <div class="page-card mb-3">
        <h6 class="fw-bold mb-3">Datos Generales</h6>
        <div class="row">
            <div class="col-md-4 dato-valor"><div class="dato-label">Médico Veterinario</div>{{ $ficha->veterinario ?: '—' }}</div>
            <div class="col-md-8 dato-valor"><div class="dato-label">Motivo de consulta</div>{{ $ficha->motivo_consulta }}</div>
        </div>
    </div>

    <div class="page-card mb-3">
        <h6 class="fw-bold mb-3">Vacunas y Desparasitación</h6>
        <div class="row">
            <div class="col-md-3 dato-valor"><div class="dato-label">Esquema completo</div>{{ $ficha->esquema_completo ? 'Sí' : 'No' }}</div>
            <div class="col-md-9 dato-valor"><div class="dato-label">Detalles de vacunas</div>{{ $ficha->vacunas_aplicadas ?: '—' }}</div>
            <div class="col-md-4 dato-valor"><div class="dato-label">Última desparasitación</div>{{ $ficha->fecha_desparasitacion?->format('d/m/Y') ?? '—' }}</div>
            <div class="col-md-4 dato-valor"><div class="dato-label">Droga</div>{{ $ficha->droga_desparasitacion ?: '—' }}</div>
            <div class="col-md-4 dato-valor"><div class="dato-label">Producto</div>{{ $ficha->producto_desparasitacion ?: '—' }}</div>
        </div>
    </div>

    <div class="page-card mb-3">
        <h6 class="fw-bold mb-3">Parámetros Fisiológicos</h6>
        <div class="row">
            <div class="col-md-2 dato-valor"><div class="dato-label">FC</div>{{ $ficha->fc ?? '—' }}</div>
            <div class="col-md-2 dato-valor"><div class="dato-label">FR</div>{{ $ficha->fr ?? '—' }}</div>
            <div class="col-md-2 dato-valor"><div class="dato-label">Temperatura</div>{{ $ficha->temperatura ?? '—' }}</div>
            <div class="col-md-2 dato-valor"><div class="dato-label">Pulso</div>{{ $ficha->pulso ?: '—' }}</div>
            <div class="col-md-2 dato-valor"><div class="dato-label">TLLC</div>{{ $ficha->tllc ?: '—' }}</div>
            <div class="col-md-2 dato-valor"><div class="dato-label">Relación c/ entorno</div>{{ $ficha->relacion_entorno ?: '—' }}</div>
        </div>
    </div>

    <div class="page-card mb-3">
        <h6 class="fw-bold mb-3">Estado de Hidratación</h6>
        <div class="row">
            <div class="col-md-3 dato-valor"><div class="dato-label">Normal</div>{{ $ficha->hidratacion_normal ? 'Sí' : 'No' }}</div>
            <div class="col-md-3 dato-valor"><div class="dato-label">Grado de deshidratación</div>{{ $ficha->grado_deshidratacion ?: '—' }}</div>
        </div>
    </div>

    <div class="page-card mb-3">
        <h6 class="fw-bold mb-3">Examen Físico</h6>
        <div class="row">
            @foreach ($examenItems as $campo => $etiqueta)
                <div class="col-md-4 dato-valor"><div class="dato-label">{{ $etiqueta }}</div>{{ $ficha->$campo ?: '—' }}</div>
            @endforeach
        </div>
    </div>

    <div class="page-card">
        <h6 class="fw-bold mb-3">Condición Corporal</h6>
        <div>{{ $ficha->condicion_corporal ? $ficha->condicion_corporal . ' - ' . $condiciones[$ficha->condicion_corporal] : '—' }}</div>
    </div>
@endsection
