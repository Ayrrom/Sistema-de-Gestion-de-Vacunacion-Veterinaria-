@extends('layouts.app')

@section('title', 'Estadísticas - Veterinaria Uncaus')

@section('content')
    @php
        $i = $indicadores;
        $diferencia = $i['consultas_mes'] - $i['consultas_mes_anterior'];
        $maxMes = max(1, collect($consultasPorMes)->max('valor'));
        $colorEspecie = ['Perro' => 'viz-perro', 'Gato' => 'viz-gato'];
        $maxCondicion = max(1, collect($condicionCorporal['filas'])->flatMap(fn ($f) => $f['por_especie'])->max());
        $maxEdad = max(1, collect($edades['filas'])->flatMap(fn ($f) => $f['por_especie'])->max());
        $maxEstado = max(1, collect($estados)->max('valor'));
        $maxMotivo = max(1, collect($motivos)->max('valor'));
        $maxHallazgo = max(1, collect($hallazgos)->max('valor'));
        $estiloEstado = [
            'Saludable' => ['viz-estado-bien', 'bi-check-circle'],
            'En tratamiento' => ['viz-estado-tratamiento', 'bi-capsule'],
            'Revisión' => ['viz-estado-revision', 'bi-arrow-repeat'],
            'Sin consultas' => ['viz-estado-sin', 'bi-dash-circle'],
        ];
    @endphp

    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
        <div>
            <div class="page-title">Estadísticas</div>
            <div class="page-subtitle mb-0">
                {{ $especie ? 'Pacientes: ' . ($especie . 's') : 'Todos los pacientes' }} · consultas de los últimos 12 meses
            </div>
        </div>
        <form method="GET" action="{{ route('estadisticas.index') }}" class="d-flex gap-2 align-items-center">
            <label for="especie" class="small text-muted mb-0">Especie</label>
            <select id="especie" name="especie" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todas</option>
                @foreach ($especies as $e)
                    <option value="{{ $e }}" {{ $especie === $e ? 'selected' : '' }}>{{ ($e . 's') }}</option>
                @endforeach
            </select>
            <noscript><button class="btn btn-sm btn-guardar">Ver</button></noscript>
        </form>
    </div>

    {{-- Indicadores --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-2">
            <div class="viz-tile">
                <div class="viz-tile-label">Pacientes</div>
                <div class="viz-tile-valor">{{ $i['total'] }}</div>
                <div class="viz-tile-detalle">{{ $i['perros'] }} perros · {{ $i['gatos'] }} gatos</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="viz-tile">
                <div class="viz-tile-label">Consultas este mes</div>
                <div class="viz-tile-valor">{{ $i['consultas_mes'] }}</div>
                <div class="viz-tile-detalle">
                    @if ($diferencia > 0)
                        <i class="bi bi-arrow-up-short"></i> {{ $diferencia }} más que el mes anterior
                    @elseif ($diferencia < 0)
                        <i class="bi bi-arrow-down-short"></i> {{ abs($diferencia) }} menos que el mes anterior
                    @else
                        Igual que el mes anterior
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="viz-tile">
                <div class="viz-tile-label">En tratamiento</div>
                <div class="viz-tile-valor">{{ $i['en_tratamiento'] }}</div>
                <div class="viz-tile-detalle">según su última consulta</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="viz-tile">
                <div class="viz-tile-label">Control anual vencido</div>
                <div class="viz-tile-valor">{{ $i['control_vencido'] }} <small>{{ $i['control_vencido_pct'] }}%</small></div>
                <div class="viz-tile-detalle">más de 12 meses sin consulta</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="viz-tile">
                <div class="viz-tile-label">Vacunación incompleta</div>
                <div class="viz-tile-valor">{{ $i['vacunacion_incompleta'] }} <small>{{ $i['vacunacion_incompleta_pct'] }}%</small></div>
                <div class="viz-tile-detalle">esquema no completo en la última ficha</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="viz-tile">
                <div class="viz-tile-label">Desparasitación vencida</div>
                <div class="viz-tile-valor">{{ $i['desparasitacion_vencida'] }} <small>{{ $i['desparasitacion_vencida_pct'] }}%</small></div>
                <div class="viz-tile-detalle">más de 3 meses o sin registro</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Consultas por mes --}}
        <div class="col-lg-8">
            <div class="page-card h-100">
                <h6 class="fw-bold mb-1">Consultas por mes</h6>
                <p class="small text-muted mb-3">{{ $totalFichasDelAnio }} consultas en los últimos 12 meses</p>

                <div class="viz-columnas" role="img" aria-label="Consultas por mes en los últimos 12 meses">
                    @foreach ($consultasPorMes as $mes)
                        @php $esUltimo = $loop->last; $esMax = $mes['valor'] === $maxMes && $mes['valor'] > 0; @endphp
                        <div class="viz-columna" title="{{ $mes['etiqueta_larga'] }}: {{ $mes['valor'] }} {{ $mes['valor'] === 1 ? 'consulta' : 'consultas' }}">
                            <div class="viz-columna-area">
                                @if ($esUltimo || $esMax)
                                    <span class="viz-columna-valor">{{ $mes['valor'] }}</span>
                                @endif
                                <div class="viz-columna-barra viz-serie" style="height: {{ $mes['valor'] ? max(2, round($mes['valor'] * 100 / $maxMes)) : 0 }}%"></div>
                            </div>
                            <span class="viz-columna-etiqueta {{ $esUltimo ? 'fw-semibold' : '' }}">{{ $mes['etiqueta'] }}</span>
                        </div>
                    @endforeach
                </div>

                <details class="mt-2 small">
                    <summary class="text-muted">Ver como tabla</summary>
                    <table class="table table-sm mt-2 mb-0">
                        <thead><tr><th>Mes</th><th class="text-end">Consultas</th></tr></thead>
                        <tbody>
                            @foreach ($consultasPorMes as $mes)
                                <tr><td>{{ $mes['etiqueta_larga'] }}</td><td class="text-end">{{ $mes['valor'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </details>
            </div>
        </div>

        {{-- Estado actual --}}
        <div class="col-lg-4">
            <div class="page-card h-100">
                <h6 class="fw-bold mb-1">Estado actual de los pacientes</h6>
                <p class="small text-muted mb-3">según su última consulta</p>
                @foreach ($estados as $estado)
                    @php [$clase, $icono] = $estiloEstado[$estado['nombre']]; @endphp
                    <div class="viz-fila" title="{{ $estado['nombre'] }}: {{ $estado['valor'] }}">
                        <span class="viz-fila-nombre"><i class="bi {{ $icono }} {{ $clase }}-texto"></i> {{ $estado['nombre'] }}</span>
                        <div class="viz-fila-pista"><div class="viz-fila-barra {{ $clase }}" style="width: {{ round($estado['valor'] * 100 / $maxEstado) }}%"></div></div>
                        <span class="viz-fila-valor">{{ $estado['valor'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Condición corporal --}}
        <div class="col-lg-6">
            <div class="page-card h-100">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold mb-1">Condición corporal</h6>
                        <p class="small text-muted mb-2">
                            {{ $condicionCorporal['evaluados'] }} pacientes evaluados ·
                            <strong>{{ $condicionCorporal['sobrepeso_pct'] }}%</strong> con sobrepeso u obesidad
                        </p>
                    </div>
                    @if (count($condicionCorporal['especies']) > 1)
                        <div class="viz-leyenda">
                            @foreach ($condicionCorporal['especies'] as $e)
                                <span><span class="viz-muestra {{ $colorEspecie[$e] }}"></span>{{ ($e . 's') }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                @foreach ($condicionCorporal['filas'] as $fila)
                    <div class="viz-grupo">
                        <div class="viz-grupo-nombre">{{ $fila['valor'] }} · {{ $fila['nombre'] }} <span class="text-muted">({{ $fila['referencia'] }})</span></div>
                        @foreach ($fila['por_especie'] as $e => $valor)
                            <div class="viz-fila viz-fila-compacta" title="{{ ($e . 's') }} {{ strtolower($fila['nombre']) }}: {{ $valor }}">
                                <span class="viz-fila-nombre small text-muted">{{ ($e . 's') }}</span>
                                <div class="viz-fila-pista"><div class="viz-fila-barra {{ $colorEspecie[$e] }}" style="width: {{ round($valor * 100 / $maxCondicion) }}%"></div></div>
                                <span class="viz-fila-valor">{{ $valor }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Edades --}}
        <div class="col-lg-6">
            <div class="page-card mb-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold mb-1">Pacientes por edad</h6>
                        <p class="small text-muted mb-2">{{ $i['castrados'] }} castrados ({{ $i['castrados_pct'] }}% del total)</p>
                    </div>
                    @if (count($edades['especies']) > 1)
                        <div class="viz-leyenda">
                            @foreach ($edades['especies'] as $e)
                                <span><span class="viz-muestra {{ $colorEspecie[$e] }}"></span>{{ ($e . 's') }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                @foreach ($edades['filas'] as $fila)
                    <div class="viz-grupo">
                        <div class="viz-grupo-nombre">{{ $fila['nombre'] }}</div>
                        @foreach ($fila['por_especie'] as $e => $valor)
                            <div class="viz-fila viz-fila-compacta" title="{{ ($e . 's') }} · {{ $fila['nombre'] }}: {{ $valor }}">
                                <span class="viz-fila-nombre small text-muted">{{ ($e . 's') }}</span>
                                <div class="viz-fila-pista"><div class="viz-fila-barra {{ $colorEspecie[$e] }}" style="width: {{ round($valor * 100 / $maxEdad) }}%"></div></div>
                                <span class="viz-fila-valor">{{ $valor }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="page-card">
                <h6 class="fw-bold mb-1">Motivos de consulta más frecuentes</h6>
                <p class="small text-muted mb-3">últimos 12 meses</p>
                @forelse ($motivos as $motivo)
                    <div class="viz-fila" title="{{ $motivo['nombre'] }}: {{ $motivo['valor'] }}">
                        <span class="viz-fila-nombre text-truncate">{{ $motivo['nombre'] }}</span>
                        <div class="viz-fila-pista"><div class="viz-fila-barra viz-serie" style="width: {{ round($motivo['valor'] * 100 / $maxMotivo) }}%"></div></div>
                        <span class="viz-fila-valor">{{ $motivo['valor'] }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Todavía no hay consultas en este período.</p>
                @endforelse
            </div>
        </div>

        {{-- Hallazgos anormales --}}
        <div class="col-lg-5">
            <div class="page-card h-100">
                <h6 class="fw-bold mb-1">Hallazgos anormales en el examen físico</h6>
                <p class="small text-muted mb-3">veces que un sistema se marcó "A" en los últimos 12 meses</p>
                @forelse ($hallazgos as $hallazgo)
                    <div class="viz-fila" title="{{ $hallazgo['nombre'] }}: {{ $hallazgo['valor'] }}">
                        <span class="viz-fila-nombre">{{ $hallazgo['nombre'] }}</span>
                        <div class="viz-fila-pista"><div class="viz-fila-barra viz-serie" style="width: {{ round($hallazgo['valor'] * 100 / $maxHallazgo) }}%"></div></div>
                        <span class="viz-fila-valor">{{ $hallazgo['valor'] }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No se registraron hallazgos anormales en este período.</p>
                @endforelse
            </div>
        </div>

        {{-- Requieren atención --}}
        <div class="col-lg-7">
            <div class="page-card h-100">
                <h6 class="fw-bold mb-1">Pacientes que requieren atención</h6>
                <p class="small text-muted mb-3">{{ $requierenAtencion->count() }} pacientes con algo pendiente · los que más pendientes tienen primero</p>
                @if ($requierenAtencion->isEmpty())
                    <p class="text-muted small mb-0">No hay pacientes con pendientes. 🎉</p>
                @else
                    <div class="table-responsive viz-tabla-scroll">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr><th>Paciente</th><th>Propietario</th><th>Pendiente</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach ($requierenAtencion->take(25) as $fila)
                                    <tr>
                                        <td><a href="{{ route('mascotas.show', $fila['mascota']) }}" class="fw-semibold">{{ $fila['mascota']->nombre }}</a> <span class="small text-muted">{{ $fila['mascota']->especie }}</span></td>
                                        <td class="small">{{ $fila['mascota']->propietario_dni }}</td>
                                        <td>
                                            @foreach ($fila['motivos'] as $motivo)
                                                <span class="badge text-bg-light border fw-normal">{{ $motivo }}</span>
                                            @endforeach
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('fichas.create', $fila['mascota']) }}" class="btn btn-sm btn-outline-success" title="Nueva consulta"><i class="bi bi-file-earmark-plus"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($requierenAtencion->count() > 25)
                        <p class="small text-muted mt-2 mb-0">Se muestran los primeros 25.</p>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
