<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historia Clínica - {{ $mascota->nombre }}</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #1f2430;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2f6fed;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            color: #2f6fed;
            margin: 0 0 3px 0;
        }
        .header p {
            font-size: 10px;
            color: #8a90a2;
            margin: 0;
        }
        .info-box {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-box td {
            vertical-align: top;
            width: 50%;
            padding: 8px;
            background-color: #f4f6fb;
        }
        .info-box h3 {
            font-size: 12px;
            margin: 0 0 6px 0;
            color: #2f6fed;
        }
        .dato { margin-bottom: 3px; }
        .dato .label { color: #8a90a2; font-size: 9px; }
        .dato .valor { font-size: 11px; }

        .consulta {
            border: 1px solid #dfe2ea;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .consulta-header {
            border-bottom: 1px solid #eceef4;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .consulta-header .fecha {
            font-size: 13px;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            color: #fff;
            background-color: #8a90a2;
        }
        .badge-saludable { background-color: #16a34a; }
        .badge-tratamiento { background-color: #b7791f; }
        .badge-revision { background-color: #2f6fed; }

        table.detalle {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.detalle td {
            padding: 3px 6px 3px 0;
            vertical-align: top;
            width: 25%;
            font-size: 10px;
        }
        table.detalle td .label {
            color: #8a90a2;
            display: block;
            font-size: 9px;
        }
        .seccion-titulo {
            font-size: 10px;
            font-weight: bold;
            color: #2f6fed;
            margin-top: 8px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .sin-consultas {
            text-align: center;
            color: #8a90a2;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🐾 Veterinaria Uncaus</h1>
        <p>Historia Clínica - Universidad Nacional del Chaco Austral</p>
    </div>

    <table class="info-box">
        <tr>
            <td>
                <h3>Datos del Paciente</h3>
                <div class="dato"><span class="label">Nombre:</span> <span class="valor">{{ $mascota->nombre }}</span></div>
                <div class="dato"><span class="label">Especie / Raza:</span> <span class="valor">{{ $mascota->especie }} · {{ $mascota->raza }}</span></div>
                <div class="dato"><span class="label">Sexo:</span> <span class="valor">{{ $mascota->sexo }}</span></div>
                <div class="dato"><span class="label">Edad / Peso:</span> <span class="valor">{{ $mascota->edad }} años · {{ $mascota->peso }} kg</span></div>
            </td>
            <td>
                <h3>Datos del Propietario</h3>
                <div class="dato"><span class="label">Nombre:</span> <span class="valor">{{ $mascota->propietario->nombre }} {{ $mascota->propietario->apellido }}</span></div>
                <div class="dato"><span class="label">DNI:</span> <span class="valor">{{ $mascota->propietario->dni }}</span></div>
                <div class="dato"><span class="label">Teléfono:</span> <span class="valor">{{ $mascota->propietario->telefono }}</span></div>
                <div class="dato"><span class="label">Localidad:</span> <span class="valor">{{ $mascota->propietario->localidad }}</span></div>
            </td>
        </tr>
    </table>

    <h3 style="color:#1f2430; border-bottom:1px solid #eceef4; padding-bottom:4px;">Historial de Consultas ({{ $fichas->count() }})</h3>

    @if ($fichas->isEmpty())
        <p class="sin-consultas">Todavía no hay consultas registradas para este paciente.</p>
    @else
        @foreach ($fichas as $ficha)
            @php
                $badgeClass = match ($ficha->estado) {
                    'Saludable' => 'badge-saludable',
                    'En tratamiento' => 'badge-tratamiento',
                    'Revisión' => 'badge-revision',
                    default => '',
                };
            @endphp
            <div class="consulta">
                <div class="consulta-header">
                    <span class="fecha">{{ $ficha->fecha_consulta->format('d/m/Y') }}</span>
                    &nbsp;·&nbsp; Dr./Dra. {{ $ficha->veterinario ?: 'No especificado' }}
                    &nbsp;·&nbsp; <span class="badge {{ $badgeClass }}">{{ $ficha->estado }}</span>
                </div>

                <div class="seccion-titulo">Motivo de consulta</div>
                <div>{{ $ficha->motivo_consulta }}</div>

                <div class="seccion-titulo">Vacunas y Desparasitación</div>
                <table class="detalle">
                    <tr>
                        <td><span class="label">Esquema completo</span>{{ $ficha->esquema_completo ? 'Sí' : 'No' }}</td>
                        <td><span class="label">Detalles vacunas</span>{{ $ficha->vacunas_aplicadas ?: '—' }}</td>
                        <td><span class="label">Última desparasitación</span>{{ $ficha->fecha_desparasitacion?->format('d/m/Y') ?? '—' }}</td>
                        <td><span class="label">Droga / Producto</span>{{ $ficha->droga_desparasitacion ?: '—' }} / {{ $ficha->producto_desparasitacion ?: '—' }}</td>
                    </tr>
                </table>

                <div class="seccion-titulo">Parámetros Fisiológicos</div>
                <table class="detalle">
                    <tr>
                        <td><span class="label">FC</span>{{ $ficha->fc ?? '—' }}</td>
                        <td><span class="label">FR</span>{{ $ficha->fr ?? '—' }}</td>
                        <td><span class="label">Temperatura</span>{{ $ficha->temperatura ?? '—' }}</td>
                        <td><span class="label">Pulso</span>{{ $ficha->pulso ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td><span class="label">TLLC</span>{{ $ficha->tllc ?: '—' }}</td>
                        <td><span class="label">Relación c/ entorno</span>{{ $ficha->relacion_entorno ?: '—' }}</td>
                        <td><span class="label">Hidratación normal</span>{{ $ficha->hidratacion_normal ? 'Sí' : 'No' }}</td>
                        <td><span class="label">Grado deshidratación</span>{{ $ficha->grado_deshidratacion ?: '—' }}</td>
                    </tr>
                </table>

                <div class="seccion-titulo">Examen Físico</div>
                <table class="detalle">
                    <tr>
                        <td><span class="label">Piel y anexos</span>{{ $ficha->piel_anexos ?: '—' }}</td>
                        <td><span class="label">Ojos</span>{{ $ficha->ojos ?: '—' }}</td>
                        <td><span class="label">Oídos</span>{{ $ficha->oidos ?: '—' }}</td>
                        <td><span class="label">Ganglios linfáticos</span>{{ $ficha->ganglios_linfaticos ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td><span class="label">Conjuntivas-mucosas</span>{{ $ficha->conjuntivas_mucosas ?: '—' }}</td>
                        <td><span class="label">Sist. cardíaco</span>{{ $ficha->sistema_cardiaco ?: '—' }}</td>
                        <td><span class="label">Sist. respiratorio</span>{{ $ficha->sistema_respiratorio ?: '—' }}</td>
                        <td><span class="label">Sist. digestivo</span>{{ $ficha->sistema_digestivo ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td><span class="label">Sist. urinario</span>{{ $ficha->sistema_urinario ?: '—' }}</td>
                        <td><span class="label">Sist. nervioso</span>{{ $ficha->sistema_nervioso ?: '—' }}</td>
                        <td><span class="label">Sist. locomotor</span>{{ $ficha->sistema_locomotor ?: '—' }}</td>
                        <td><span class="label">Condición corporal</span>{{ $ficha->condicion_corporal ?? '—' }} / 5</td>
                    </tr>
                </table>
            </div>
        @endforeach
    @endif
</body>
</html>
