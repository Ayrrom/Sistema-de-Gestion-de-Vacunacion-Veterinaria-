@php
    $m = $mascota ?? null;
@endphp

<h6 class="fw-bold mb-3">Datos del Paciente</h6>

<div class="row g-3">
    <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre *</label>
        <input
            type="text"
            class="form-control @error('nombre') is-invalid @enderror"
            id="nombre"
            name="nombre"
            placeholder="Nombre del paciente"
            value="{{ old('nombre', $m->nombre ?? '') }}"
        >
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="especie" class="form-label">Especie *</label>
        @php $especie = old('especie', $m->especie ?? ''); @endphp
        <select class="form-select @error('especie') is-invalid @enderror" id="especie" name="especie">
            <option value="" disabled {{ $especie === '' ? 'selected' : '' }}>Seleccione especie</option>
            @foreach (['Perro', 'Gato'] as $opcion)
                <option value="{{ $opcion }}" {{ $especie === $opcion ? 'selected' : '' }}>{{ $opcion }}</option>
            @endforeach
        </select>
        @error('especie') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="raza" class="form-label">Raza *</label>
        <input
            type="text"
            class="form-control @error('raza') is-invalid @enderror"
            id="raza"
            name="raza"
            placeholder="Raza del paciente"
            value="{{ old('raza', $m->raza ?? '') }}"
        >
        @error('raza') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="sexo" class="form-label">Sexo *</label>
        @php $sexo = old('sexo', $m->sexo ?? ''); @endphp
        <select class="form-select @error('sexo') is-invalid @enderror" id="sexo" name="sexo">
            <option value="" disabled {{ $sexo === '' ? 'selected' : '' }}>Seleccione sexo</option>
            <option value="Macho" {{ $sexo === 'Macho' ? 'selected' : '' }}>Macho</option>
            <option value="Hembra" {{ $sexo === 'Hembra' ? 'selected' : '' }}>Hembra</option>
        </select>
        @error('sexo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="peso" class="form-label">Peso (kg) *</label>
        <input
            type="number" step="0.01" min="0"
            class="form-control @error('peso') is-invalid @enderror"
            id="peso"
            name="peso"
            placeholder="Peso en kilogramos"
            value="{{ old('peso', $m->peso ?? '') }}"
        >
        @error('peso') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="edad" class="form-label">Edad (años) *</label>
        <input
            type="number" min="0"
            class="form-control @error('edad') is-invalid @enderror"
            id="edad"
            name="edad"
            placeholder="Edad en años"
            value="{{ old('edad', $m->edad ?? '') }}"
        >
        @error('edad') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="tipo_pelaje" class="form-label">Tipo de pelaje *</label>
        <input
            type="text"
            class="form-control @error('tipo_pelaje') is-invalid @enderror"
            id="tipo_pelaje"
            name="tipo_pelaje"
            placeholder="Ej: Corto, Largo"
            value="{{ old('tipo_pelaje', $m->tipo_pelaje ?? '') }}"
        >
        @error('tipo_pelaje') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
        <input
            type="date"
            class="form-control @error('fecha_nacimiento') is-invalid @enderror"
            id="fecha_nacimiento"
            name="fecha_nacimiento"
            value="{{ old('fecha_nacimiento', optional($m?->fecha_nacimiento)->format('Y-m-d')) }}"
        >
        @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="color" class="form-label">Color</label>
        <input
            type="text"
            class="form-control @error('color') is-invalid @enderror"
            id="color"
            name="color"
            placeholder="Color del pelaje"
            value="{{ old('color', $m->color ?? '') }}"
        >
        @error('color') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="procedencia" class="form-label">Procedencia</label>
        <input
            type="text"
            class="form-control @error('procedencia') is-invalid @enderror"
            id="procedencia"
            name="procedencia"
            placeholder="Ej: Adopción, Compra, Vía pública"
            value="{{ old('procedencia', $m->procedencia ?? '') }}"
        >
        @error('procedencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
            <input
                type="checkbox"
                class="form-check-input"
                id="castrado"
                name="castrado"
                value="1"
                {{ old('castrado', $m->castrado ?? false) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="castrado">Castrado / Esterilizado</label>
        </div>
    </div>

    <div class="col-12">
        <label for="senas_particulares" class="form-label">Señas particulares</label>
        <textarea
            class="form-control @error('senas_particulares') is-invalid @enderror"
            id="senas_particulares"
            name="senas_particulares"
            rows="2"
            placeholder="Marcas, cicatrices u otras características"
        >{{ old('senas_particulares', $m->senas_particulares ?? '') }}</textarea>
        @error('senas_particulares') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-guardar">Guardar</button>
    <a href="{{ route('mascotas.index') }}" class="btn btn-cancelar">Cancelar</a>
</div>
