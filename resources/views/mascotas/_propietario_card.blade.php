<div class="page-card h-100">
    <h6 class="fw-bold mb-3">Datos del Propietario</h6>

    <div class="mb-2">
        <div class="text-muted small">DNI</div>
        <div>{{ $propietario->dni }}</div>
    </div>
    <div class="mb-2">
        <div class="text-muted small">Nombre</div>
        <div>{{ $propietario->nombre }}</div>
    </div>
    <div class="mb-2">
        <div class="text-muted small">Apellido</div>
        <div>{{ $propietario->apellido }}</div>
    </div>
    <div class="mb-2">
        <div class="text-muted small">Dirección</div>
        <div>{{ $propietario->calle }} {{ $propietario->altura }}</div>
    </div>
    <div class="mb-2">
        <div class="text-muted small">Localidad</div>
        <div>{{ $propietario->localidad }}</div>
    </div>
    <div class="mb-3">
        <div class="text-muted small">Teléfonos</div>
        <div>{{ $propietario->telefono }}</div>
    </div>

    @if ($buscarOtro ?? false)
        <a href="{{ route('mascotas.create') }}" class="btn btn-guardar w-100">Buscar otro propietario</a>
    @endif
</div>
