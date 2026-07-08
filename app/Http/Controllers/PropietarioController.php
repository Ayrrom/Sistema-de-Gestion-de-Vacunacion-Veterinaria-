<?php

namespace App\Http\Controllers;

use App\Models\Propietario;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class PropietarioController extends Controller
{
    /**
     * Listado de propietarios.
     */
    public function index()
    {
        $propietarios = Propietario::orderBy('apellido')->orderBy('nombre')->get();

        return view('propietarios.index', compact('propietarios'));
    }

    /**
     * Formulario de alta.
     */
    public function create()
    {
        return view('propietarios.create');
    }

    /**
     * Guardar un propietario nuevo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dni' => ['required', 'string', 'max:20', 'unique:propietarios,dni'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'localidad' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:50'],
            'calle' => ['required', 'string', 'max:255'],
            'altura' => ['required', 'string', 'max:20'],
        ], [
            'dni.required' => 'Ingresá el DNI del propietario.',
            'dni.unique' => 'Ya existe un propietario registrado con ese DNI.',
            'nombre.required' => 'Ingresá el nombre.',
            'apellido.required' => 'Ingresá el apellido.',
            'localidad.required' => 'Ingresá la localidad.',
            'telefono.required' => 'Ingresá un teléfono de contacto.',
            'calle.required' => 'Ingresá la calle.',
            'altura.required' => 'Ingresá la altura.',
        ]);

        Propietario::create($validated);

        return redirect()->route('propietarios.index')
            ->with('success', 'Propietario registrado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Propietario $propietario)
    {
        return view('propietarios.edit', compact('propietario'));
    }

    /**
     * Actualizar un propietario existente.
     * El DNI no se puede modificar porque es la clave primaria y otras
     * tablas (mascotas) lo usan como referencia.
     */
    public function update(Request $request, Propietario $propietario)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'localidad' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:50'],
            'calle' => ['required', 'string', 'max:255'],
            'altura' => ['required', 'string', 'max:20'],
        ], [
            'nombre.required' => 'Ingresá el nombre.',
            'apellido.required' => 'Ingresá el apellido.',
            'localidad.required' => 'Ingresá la localidad.',
            'telefono.required' => 'Ingresá un teléfono de contacto.',
            'calle.required' => 'Ingresá la calle.',
            'altura.required' => 'Ingresá la altura.',
        ]);

        $propietario->update($validated);

        return redirect()->route('propietarios.index')
            ->with('success', 'Propietario actualizado correctamente.');
    }

    /**
     * Eliminar un propietario.
     */
    public function destroy(Propietario $propietario)
    {
        try {
            $propietario->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'No se puede eliminar: el propietario tiene mascotas registradas.');
        }

        return redirect()->route('propietarios.index')
            ->with('success', 'Propietario eliminado correctamente.');
    }
}
