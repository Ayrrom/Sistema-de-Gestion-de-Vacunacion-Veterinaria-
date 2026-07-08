@php
    $p = $propietario ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="dni" class="form-label">DNI *</label>
        <input
            type="text"
            class="form-control @error('dni') is-invalid @enderror"
            id="dni"
            name="dni"
            placeholder="Ingrese DNI"
            value="{{ old('dni', $p->dni ?? '') }}"
            {{ $p ? 'readonly' : '' }}
        >
        @error('dni')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($p)
            <div class="form-text">El DNI no se puede modificar.</div>
        @endif
    </div>

    <div class="col-md-6">
        <label for="telefono" class="form-label">Teléfonos *</label>
        <input
            type="text"
            class="form-control @error('telefono') is-invalid @enderror"
            id="telefono"
            name="telefono"
            placeholder="Ingrese teléfono"
            value="{{ old('telefono', $p->telefono ?? '') }}"
        >
        @error('telefono')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre *</label>
        <input
            type="text"
            class="form-control @error('nombre') is-invalid @enderror"
            id="nombre"
            name="nombre"
            placeholder="Ingrese nombre"
            value="{{ old('nombre', $p->nombre ?? '') }}"
        >
        @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="apellido" class="form-label">Apellido *</label>
        <input
            type="text"
            class="form-control @error('apellido') is-invalid @enderror"
            id="apellido"
            name="apellido"
            placeholder="Ingrese apellido"
            value="{{ old('apellido', $p->apellido ?? '') }}"
        >
        @error('apellido')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="calle" class="form-label">Calle *</label>
        <input
            type="text"
            class="form-control @error('calle') is-invalid @enderror"
            id="calle"
            name="calle"
            placeholder="Ingrese calle"
            value="{{ old('calle', $p->calle ?? '') }}"
        >
        @error('calle')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2">
        <label for="altura" class="form-label">Altura *</label>
        <input
            type="text"
            class="form-control @error('altura') is-invalid @enderror"
            id="altura"
            name="altura"
            placeholder="N°"
            value="{{ old('altura', $p->altura ?? '') }}"
        >
        @error('altura')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="localidad" class="form-label">Localidad *</label>
        <input
            type="text"
            class="form-control @error('localidad') is-invalid @enderror"
            id="localidad"
            name="localidad"
            placeholder="Ingrese localidad"
            value="{{ old('localidad', $p->localidad ?? '') }}"
        >
        @error('localidad')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-guardar">Guardar</button>
    <a href="{{ route('propietarios.index') }}" class="btn btn-cancelar">Cancelar</a>
</div>
