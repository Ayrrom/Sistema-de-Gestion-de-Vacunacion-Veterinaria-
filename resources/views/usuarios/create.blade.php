@extends('layouts.app')

@section('title', 'Nuevo usuario - Veterinaria Uncaus')

@section('content')
    <div class="page-title">Nuevo usuario</div>
    <div class="page-subtitle">Dar de alta a una persona para que pueda ingresar al sistema</div>

    <div class="page-card" style="max-width: 720px;">
        <form method="POST" action="{{ route('usuarios.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nombre y apellido *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" placeholder="Ingrese el nombre" value="{{ old('name') }}" autofocus>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="dni" class="form-label">DNI *</label>
                    <input type="text" class="form-control @error('dni') is-invalid @enderror"
                           id="dni" name="dni" maxlength="8" inputmode="numeric" pattern="[0-9]*" title="Solo números" data-solo-numeros placeholder="Ingrese el DNI" value="{{ old('dni') }}">
                    @error('dni') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="rol" class="form-label">Rol *</label>
                    @php $rolSeleccionado = old('rol', 'estudiante'); @endphp
                    <select class="form-select @error('rol') is-invalid @enderror" id="rol" name="rol">
                        @foreach ($roles as $valor => $nombre)
                            <option value="{{ $valor }}" {{ $rolSeleccionado === $valor ? 'selected' : '' }}>{{ $nombre }}</option>
                        @endforeach
                    </select>
                    @error('rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6" data-solo-rol="veterinario">
                    <label for="catedra" class="form-label">Cátedra</label>
                    <input type="text" class="form-control @error('catedra') is-invalid @enderror"
                           id="catedra" name="catedra" placeholder="Ej: Clínica de pequeños animales" value="{{ old('catedra') }}">
                    @error('catedra') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6" data-solo-rol="estudiante">
                    <label for="veterinario_id" class="form-label">Veterinario a cargo</label>
                    <select class="form-select @error('veterinario_id') is-invalid @enderror" id="veterinario_id" name="veterinario_id">
                        <option value="">Sin asignar</option>
                        @foreach ($veterinarios as $veterinario)
                            <option value="{{ $veterinario->id }}" {{ (string) old('veterinario_id') === (string) $veterinario->id ? 'selected' : '' }}>{{ $veterinario->nombre }}</option>
                        @endforeach
                    </select>
                    @error('veterinario_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="alert alert-info py-2 small mt-3">
                La contraseña inicial va a ser el número de DNI. El usuario va a tener que cambiarla la primera vez que ingrese.
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-guardar">Crear usuario</button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-cancelar">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        // Muestra "Cátedra" solo para veterinarios y "Veterinario a cargo" solo para estudiantes.
        (function () {
            var rol = document.getElementById('rol');
            function actualizar() {
                document.querySelectorAll('[data-solo-rol]').forEach(function (el) {
                    el.style.display = el.dataset.soloRol === rol.value ? '' : 'none';
                });
            }
            rol.addEventListener('change', actualizar);
            actualizar();
        })();
    </script>
@endsection
