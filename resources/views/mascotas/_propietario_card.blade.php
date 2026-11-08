<div class="page-card h-100 datos-propietario">
    <h6 class="fw-bold mb-3">Datos del Propietario</h6>

    <div class="mb-2">
        <div class="text-muted small">DNI</div>
        <div class="dato-propietario-valor">{{ $propietario->dni }}</div>
    </div>
    <div class="mb-2">
        <div class="text-muted small">Nombre</div>
        <div class="dato-propietario-valor">{{ $propietario->nombre }}</div>
    </div>
    <div class="mb-2">
        <div class="text-muted small">Apellido</div>
        <div class="dato-propietario-valor">{{ $propietario->apellido }}</div>
    </div>
    <div class="mb-2">
        <div class="text-muted small">Dirección</div>
        <div class="dato-propietario-valor">{{ $propietario->calle }} {{ $propietario->altura }}</div>
    </div>
    <div class="mb-2">
        <div class="text-muted small">Localidad</div>
        <div class="dato-propietario-valor">{{ $propietario->localidad }}</div>
    </div>
    <div class="mb-3">
        <div class="text-muted small">Teléfonos</div>
        <div class="dato-propietario-valor">{{ $propietario->telefono }}</div>
    </div>

    @if ($buscarOtro ?? false)
        <a href="{{ route('mascotas.create') }}" class="btn btn-guardar w-100">Buscar otro propietario</a>
    @endif
</div>
