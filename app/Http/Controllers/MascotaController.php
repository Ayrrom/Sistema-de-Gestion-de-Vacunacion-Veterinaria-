<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Propietario;
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'propietario_dni' => ['required', 'string', 'exists:propietarios,dni'],
            'nombre' => ['required', 'string', 'max:255'],
            'especie' => ['required', 'string', 'max:100'],
            'raza' => ['required', 'string', 'max:100'],
            'sexo' => ['required', 'string', 'max:20'],
            'peso' => ['required', 'numeric', 'min:0'],
            'edad' => ['required', 'integer', 'min:0'],
            'tipo_pelaje' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'color' => ['nullable', 'string', 'max:100'],
            'procedencia' => ['nullable', 'string', 'max:255'],
            'senas_particulares' => ['nullable', 'string'],
        ], [
            'propietario_dni.exists' => 'El propietario seleccionado no es válido.',
            'nombre.required' => 'Ingresá el nombre de la mascota.',
            'especie.required' => 'Seleccioná la especie.',
            'raza.required' => 'Ingresá la raza.',
            'sexo.required' => 'Seleccioná el sexo.',
            'peso.required' => 'Ingresá el peso.',
            'peso.numeric' => 'El peso tiene que ser un número.',
            'edad.required' => 'Ingresá la edad.',
            'edad.integer' => 'La edad tiene que ser un número entero.',
            'tipo_pelaje.required' => 'Ingresá el tipo de pelaje.',
        ]);

        $validated['castrado'] = $request->boolean('castrado');

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
    public function update(Request $request, Mascota $mascota)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'especie' => ['required', 'string', 'max:100'],
            'raza' => ['required', 'string', 'max:100'],
            'sexo' => ['required', 'string', 'max:20'],
            'peso' => ['required', 'numeric', 'min:0'],
            'edad' => ['required', 'integer', 'min:0'],
            'tipo_pelaje' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'color' => ['nullable', 'string', 'max:100'],
            'procedencia' => ['nullable', 'string', 'max:255'],
            'senas_particulares' => ['nullable', 'string'],
        ], [
            'nombre.required' => 'Ingresá el nombre de la mascota.',
            'especie.required' => 'Seleccioná la especie.',
            'raza.required' => 'Ingresá la raza.',
            'sexo.required' => 'Seleccioná el sexo.',
            'peso.required' => 'Ingresá el peso.',
            'peso.numeric' => 'El peso tiene que ser un número.',
            'edad.required' => 'Ingresá la edad.',
            'edad.integer' => 'La edad tiene que ser un número entero.',
            'tipo_pelaje.required' => 'Ingresá el tipo de pelaje.',
        ]);

        $validated['castrado'] = $request->boolean('castrado');

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
