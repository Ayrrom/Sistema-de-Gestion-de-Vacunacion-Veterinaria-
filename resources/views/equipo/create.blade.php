@extends('layouts.app')

@section('title', 'Alta en el equipo - Veterinaria Uncaus')

@section('content')
    <a href="{{ route('equipo.index') }}" class="text-decoration-none d-inline-flex align-items-center gap-1 mb-3" style="font-size:0.9rem; font-weight:600; color:#1f2430;">
        <i class="bi bi-arrow-left"></i> Volver al equipo
    </a>

    <div class="page-title">Dar de alta en el equipo</div>
    <div class="page-subtitle">La persona va a poder ingresar con su DNI como contraseña inicial</div>

    <div class="page-card" style="max-width: 720px;">
        <form method="POST" action="{{ route('equipo.store') }}">
            @csrf

            @php $rolElegido = old('rol', $rol); @endphp
            <div class="mb-3">
                <label class="form-label d-block">Tipo *</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="rol" id="rol_estudiante" value="estudiante" {{ $rolElegido === 'estudiante' ? 'checked' : '' }}>
                    <label class="form-check-label" for="rol_estudiante">Estudiante</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="rol" id="rol_veterinario" value="veterinario" {{ $rolElegido === 'veterinario' ? 'checked' : '' }}>
                    <label class="form-check-label" for="rol_veterinario">Veterinario</label>
                </div>
                @error('rol') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nombre y apellido *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" {{ \App\Support\Campos::atributos('name') }} placeholder="Ingrese el nombre" value="{{ old('name') }}" autofocus>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="dni" class="form-label">DNI *</label>
                    <input type="text" class="form-control @error('dni') is-invalid @enderror"
                           id="dni" name="dni" maxlength="8" inputmode="numeric" pattern="[0-9]*" title="Solo números" data-solo-numeros
                           placeholder="Ingrese el DNI" value="{{ old('dni') }}">
                    @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6" data-solo-rol="estudiante">
                    <label for="veterinario_id" class="form-label">Veterinario a cargo</label>
                    @php $vetElegido = (string) old('veterinario_id', $miVeterinario?->id ?? ''); @endphp
                    <select class="form-select @error('veterinario_id') is-invalid @enderror" id="veterinario_id" name="veterinario_id">
                        <option value="">Sin asignar</option>
                        @foreach ($veterinarios as $veterinario)
                            <option value="{{ $veterinario->id }}" {{ $vetElegido === (string) $veterinario->id ? 'selected' : '' }}>
                                {{ $veterinario->nombre }}{{ $miVeterinario?->id === $veterinario->id ? ' (vos)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('veterinario_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6" data-solo-rol="veterinario">
                    <label for="catedra" class="form-label">Cátedra</label>
                    <input type="text" class="form-control @error('catedra') is-invalid @enderror"
                           id="catedra" name="catedra" {{ \App\Support\Campos::atributos('catedra') }} placeholder="Ej: Clínica de pequeños animales" value="{{ old('catedra') }}">
                    @error('catedra') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="alert alert-info py-2 small mt-3">
                La contraseña inicial va a ser el número de DNI y se le va a pedir que la cambie la primera vez que ingrese.
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-guardar">Dar de alta</button>
                <a href="{{ route('equipo.index') }}" class="btn btn-cancelar">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        // Muestra "Veterinario a cargo" solo para estudiantes y "Cátedra" solo para veterinarios.
        (function () {
            function actualizar() {
                var rol = document.querySelector('input[name="rol"]:checked');
                document.querySelectorAll('[data-solo-rol]').forEach(function (el) {
                    el.style.display = rol && el.dataset.soloRol === rol.value ? '' : 'none';
                });
            }
            document.querySelectorAll('input[name="rol"]').forEach(function (r) { r.addEventListener('change', actualizar); });
            actualizar();
        })();
    </script>
@endsection
