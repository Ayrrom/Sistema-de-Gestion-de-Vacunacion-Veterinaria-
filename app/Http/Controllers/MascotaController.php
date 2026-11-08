<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Propietario;
use App\Http\Requests\MascotaRequest;
use Illuminate\Http\Request;

class MascotaController extends Controller
{
    /**
     * Listado de mascotas (pacientes).
     */
    public function index()
    {
        $mascotas = Mascota::with('propietario')->orderBy('nombre')->get();

        return view('mascotas.index', compact('mascotas'));
    }

    /**
     * Paso 1: buscar propietario, o paso 2: mostrar el formulario del
     * paciente si ya se encontró un propietario (?dni=...).
     */
    public function create(Request $request)
    {
        $dni = $request->query('dni');

        if (!$dni) {
            return view('mascotas.buscar');
        }

        $propietario = Propietario::find($dni);

        if (!$propietario) {
            return redirect()->route('mascotas.create')
                ->with('error', 'No se encontró ningún propietario con ese DNI. Primero tenés que registrarlo.');
        }

        return view('mascotas.create', compact('propietario'));
    }

    /**
     * Guardar una mascota nueva.
     */
    public function store(MascotaRequest $request)
    {
        $validated = $request->datos();

        Mascota::create($validated);

        return redirect()->route('mascotas.index')
            ->with('success', 'Paciente registrado correctamente.');
    }

    /**
     * Ver el detalle de una mascota.
     */
    public function show(Mascota $mascota)
    {
        $mascota->load('propietario');
        $ultimaFicha = $mascota->fichasClinicas()->orderByDesc('fecha_consulta')->first();

        return view('mascotas.show', compact('mascota', 'ultimaFicha'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Mascota $mascota)
    {
        $propietario = $mascota->propietario;

        return view('mascotas.edit', compact('mascota', 'propietario'));
    }

    /**
     * Actualizar una mascota existente.
     */
    public function update(MascotaRequest $request, Mascota $mascota)
    {
        $validated = $request->datos();

        $mascota->update($validated);

        return redirect()->route('mascotas.index')
            ->with('success', 'Paciente actualizado correctamente.');
    }

    /**
     * Eliminar una mascota.
     */
    public function destroy(Mascota $mascota)
    {
        $mascota->delete();

        return redirect()->route('mascotas.index')
            ->with('success', 'Paciente eliminado correctamente.');
    }
}
