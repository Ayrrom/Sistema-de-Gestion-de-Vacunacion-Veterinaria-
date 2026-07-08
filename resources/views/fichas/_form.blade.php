@php
    $f = $ficha ?? null;

    $examenItems = [
        'piel_anexos' => '1. Piel y anexos',
        'ojos' => '2. Ojos',
        'oidos' => '3. Oídos',
        'ganglios_linfaticos' => '4. Ganglios linfáticos',
        'conjuntivas_mucosas' => '5. Conjuntivas-mucosas',
        'sistema_cardiaco' => '6. Sistema cardíaco',
        'sistema_respiratorio' => '7. Sistema respiratorio',
        'sistema_digestivo' => '8. Sistema digestivo',
        'sistema_urinario' => '9. Sistema urinario',
        'sistema_nervioso' => '10. Sistema nervioso',
        'sistema_locomotor' => '11. Sistema locomotor',
    ];

    $condiciones = [
        1 => 'Caquéctico',
        2 => 'Delgado',
        3 => 'Ideal',
        4 => 'Sobrepeso',
        5 => 'Obeso',
    ];
@endphp

<div class="page-card mb-3">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="fecha_consulta" class="form-label">Fecha *</label>
            <input
                type="date"
                class="form-control @error('fecha_consulta') is-invalid @enderror"
                id="fecha_consulta"
                name="fecha_consulta"
                value="{{ old('fecha_consulta', optional($f?->fecha_consulta)->format('Y-m-d') ?? date('Y-m-d')) }}"
            >
            @error('fecha_consulta') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label for="veterinario" class="form-label">Médico Veterinario</label>
            <input type="text" class="form-control" id="veterinario" name="veterinario" placeholder="Nombre del médico veterinario" value="{{ old('veterinario', $f->veterinario ?? '') }}">
        </div>
    </div>
</div>

<div class="page-card mb-3">
    <h6 class="fw-bold mb-3">VACUNAS</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label d-block">Esquema completo</label>
            @php $esquema = old('esquema_completo', isset($f) ? ($f->esquema_completo ? '1' : '0') : null); @endphp
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="esquema_completo" id="esquema_si" value="1" {{ $esquema === '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="esquema_si">Sí</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="esquema_completo" id="esquema_no" value="0" {{ $esquema === '0' || $esquema === null ? 'checked' : '' }}>
                <label class="form-check-label" for="esquema_no">No</label>
            </div>
        </div>
        <div class="col-md-6">
            <label for="vacunas_aplicadas" class="form-label">Detalles</label>
            <input type="text" class="form-control" id="vacunas_aplicadas" name="vacunas_aplicadas" placeholder="Detalles de vacunas" value="{{ old('vacunas_aplicadas', $f->vacunas_aplicadas ?? '') }}">
        </div>
    </div>
</div>

<div class="page-card mb-3">
    <h6 class="fw-bold mb-3">DESPARASITACIÓN</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <label for="fecha_desparasitacion" class="form-label">Última desparasitación</label>
            <input type="date" class="form-control" id="fecha_desparasitacion" name="fecha_desparasitacion" value="{{ old('fecha_desparasitacion', optional($f?->fecha_desparasitacion)->format('Y-m-d')) }}">
        </div>
        <div class="col-md-4">
            <label for="droga_desparasitacion" class="form-label">Droga</label>
            <input type="text" class="form-control" id="droga_desparasitacion" name="droga_desparasitacion" placeholder="Nombre de la droga" value="{{ old('droga_desparasitacion', $f->droga_desparasitacion ?? '') }}">
        </div>
        <div class="col-md-4">
            <label for="producto_desparasitacion" class="form-label">Producto</label>
            <input type="text" class="form-control" id="producto_desparasitacion" name="producto_desparasitacion" placeholder="Nombre del producto" value="{{ old('producto_desparasitacion', $f->producto_desparasitacion ?? '') }}">
        </div>
    </div>
</div>

<div class="page-card mb-3">
    <h6 class="fw-bold mb-3">ANAMNESIS</h6>
    <div class="row g-3">
        <div class="col-md-8">
            <label for="motivo_consulta" class="form-label">Motivo de la consulta *</label>
            <textarea class="form-control @error('motivo_consulta') is-invalid @enderror" id="motivo_consulta" name="motivo_consulta" rows="2" placeholder="Describa el motivo de la consulta">{{ old('motivo_consulta', $f->motivo_consulta ?? '') }}</textarea>
            @error('motivo_consulta') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="estado" class="form-label">Estado del paciente *</label>
            @php $estado = old('estado', $f->estado ?? ''); @endphp
            <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado">
                <option value="" disabled {{ $estado === '' ? 'selected' : '' }}>Seleccione estado</option>
                @foreach (['Saludable', 'En tratamiento', 'Revisión'] as $opcion)
                    <option value="{{ $opcion }}" {{ $estado === $opcion ? 'selected' : '' }}>{{ $opcion }}</option>
                @endforeach
            </select>
            @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="page-card mb-3">
    <h6 class="fw-bold mb-3">PARÁMETROS FISIOLÓGICOS</h6>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label for="fc" class="form-label">FC</label>
            <input type="number" class="form-control" id="fc" name="fc" placeholder="FC" value="{{ old('fc', $f->fc ?? '') }}">
        </div>
        <div class="col-md-4">
            <label for="temperatura" class="form-label">Temperatura (°C)</label>
            <input type="number" step="0.1" class="form-control" id="temperatura" name="temperatura" placeholder="Temperatura" value="{{ old('temperatura', $f->temperatura ?? '') }}">
        </div>
        <div class="col-md-4">
            <label for="tllc" class="form-label">TLLC</label>
            <input type="text" class="form-control" id="tllc" name="tllc" placeholder="TLLC" value="{{ old('tllc', $f->tllc ?? '') }}">
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label for="fr" class="form-label">FR</label>
            <input type="number" class="form-control" id="fr" name="fr" placeholder="FR" value="{{ old('fr', $f->fr ?? '') }}">
        </div>
        <div class="col-md-4">
            <label for="pulso" class="form-label">Pulso</label>
            <input type="text" class="form-control" id="pulso" name="pulso" placeholder="Pulso" value="{{ old('pulso', $f->pulso ?? '') }}">
        </div>
        <div class="col-md-4">
            <label for="relacion_entorno" class="form-label">Relación al entorno</label>
            <input type="text" class="form-control" id="relacion_entorno" name="relacion_entorno" placeholder="Relación al entorno" value="{{ old('relacion_entorno', $f->relacion_entorno ?? '') }}">
        </div>
    </div>
</div>

<div class="page-card mb-3">
    <h6 class="fw-bold mb-3">ESTADO DE HIDRATACIÓN</h6>
    <div class="mb-3">
        <label class="form-label d-block">Normal</label>
        @php $hidratacionNormal = old('hidratacion_normal', isset($f) ? ($f->hidratacion_normal ? '1' : '0') : '1'); @endphp
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="hidratacion_normal" id="hidratacion_si" value="1" {{ $hidratacionNormal === '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="hidratacion_si">Sí</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="hidratacion_normal" id="hidratacion_no" value="0" {{ $hidratacionNormal === '0' ? 'checked' : '' }}>
            <label class="form-check-label" for="hidratacion_no">No</label>
        </div>
    </div>
    <div>
        <label class="form-label d-block">DH (Deshidratación)</label>
        @php $grado = old('grado_deshidratacion', $f->grado_deshidratacion ?? ''); @endphp
        @foreach (['1-5%', '6-7%', '8-9%', '+10%'] as $opcion)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="grado_deshidratacion" id="dh_{{ $loop->index }}" value="{{ $opcion }}" {{ $grado === $opcion ? 'checked' : '' }}>
                <label class="form-check-label" for="dh_{{ $loop->index }}">{{ $opcion }}</label>
            </div>
        @endforeach
    </div>
</div>

<div class="page-card mb-3">
    <h6 class="fw-bold mb-3">CONDICIÓN CORPORAL</h6>
    <p class="text-muted small mb-3">Seleccione el score de condición corporal del paciente</p>
    <style>
        .condicion-opcion {
            cursor: pointer;
            width: 90px;
            border: 1px solid #dfe2ea;
            border-radius: 10px;
            padding: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }
        .condicion-opcion:hover {
            border-color: #2f6fed;
        }
        .condicion-opcion.selected {
            border-color: #2f6fed;
            background-color: #eaf0fe;
        }
        .condicion-opcion input {
            display: none;
        }
        .condicion-opcion img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
            display: block;
        }
    </style>

    @php
        $condicionSeleccionada = (string) old('condicion_corporal', $f->condicion_corporal ?? '');

    @endphp

    <div class="condicion-set d-flex gap-2 flex-wrap" data-especie="Gato">
        @foreach ($condiciones as $valor => $etiqueta)
            <label class="condicion-opcion {{ $condicionSeleccionada === (string) $valor ? 'selected' : '' }}">
                <input type="radio" name="condicion_corporal" value="{{ $valor }}" onchange="document.querySelectorAll('.condicion-opcion').forEach(el => el.classList.remove('selected')); this.closest('.condicion-opcion').classList.add('selected');" {{ $condicionSeleccionada === (string) $valor ? 'checked' : '' }}>
                <img src="{{ asset('images/condicion/gato-' . $valor . '.jpg') }}" alt="Condición corporal {{ $valor }}">
            </label>
        @endforeach
    </div>

    <div class="condicion-set d-flex gap-2 flex-wrap" data-especie="Perro" style="display: none;">
        @foreach ($condiciones as $valor => $etiqueta)
            <label class="condicion-opcion {{ $condicionSeleccionada === (string) $valor ? 'selected' : '' }}">
                <input type="radio" name="condicion_corporal" value="{{ $valor }}" onchange="document.querySelectorAll('.condicion-opcion').forEach(el => el.classList.remove('selected')); this.closest('.condicion-opcion').classList.add('selected');" {{ $condicionSeleccionada === (string) $valor ? 'checked' : '' }}>
                <img src="{{ asset('images/condicion/perro-' . $valor . '.jpg') }}" alt="Condición corporal {{ $valor }}">
            </label>
        @endforeach
    </div>

    <script>
        (function () {
            function actualizarSetCondicion() {
                var especieSelect = document.getElementById('especie');
                if (!especieSelect) return;
                var especie = especieSelect.value;
                var sets = document.querySelectorAll('.condicion-set');
                var algunoVisible = false;

                sets.forEach(function (set) {
                    if (set.dataset.especie === especie) {
                        set.style.display = 'flex';
                        algunoVisible = true;
                    } else {
                        set.style.display = 'none';
                    }
                });

                // Si la especie no es Perro ni Gato, mostramos el set de Perro como genérico.
                if (!algunoVisible) {
                    var setPerro = document.querySelector('.condicion-set[data-especie="Perro"]');
                    if (setPerro) setPerro.style.display = 'flex';
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                actualizarSetCondicion();
                var especieSelect = document.getElementById('especie');
                if (especieSelect) especieSelect.addEventListener('change', actualizarSetCondicion);
            });
        })();
    </script>
</div>

<div class="page-card mb-3">
    <h6 class="fw-bold mb-3">EXAMEN FÍSICO</h6>
    @foreach ($examenItems as $campo => $etiqueta)
        @php $valor = old($campo, $f->$campo ?? ''); @endphp
        <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
            <span>{{ $etiqueta }}</span>
            <div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="{{ $campo }}" id="{{ $campo }}_n" value="N" {{ $valor === 'N' ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $campo }}_n">N</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="{{ $campo }}" id="{{ $campo }}_a" value="A" {{ $valor === 'A' ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $campo }}_a">A</label>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">Guardar Ficha Clínica</button>
    <a href="{{ route('mascotas.show', $mascota) }}" class="btn btn-success">Cancelar</a>
</div>
