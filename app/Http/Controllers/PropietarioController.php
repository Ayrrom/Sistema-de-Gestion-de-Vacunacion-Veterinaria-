<?php

namespace App\Http\Controllers;

use App\Models\Propietario;
use App\Models\Veterinario;
use App\Http\Requests\PropietarioRequest;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class PropietarioController extends Controller
{
    /**
     * Listado de propietarios.
     */
    public function index()
    {
        $propietarios = Propietario::with('veterinario')->orderBy('apellido')->orderBy('nombre')->get();

        return view('propietarios.index', compact('propietarios'));
    }

    /**
     * Formulario de alta.
     */
    public function create()
    {
        $veterinarios = Veterinario::orderBy('nombre')->get();

        return view('propietarios.create', compact('veterinarios'));
    }

    /**
     * Guardar un propietario nuevo.
     */
    public function store(PropietarioRequest $request)
    {
        $validated = $request->validated();

        Propietario::create($validated);

        return redirect()->route('propietarios.index')
            ->with('success', 'Propietario registrado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Propietario $propietario)
    {
        $veterinarios = Veterinario::orderBy('nombre')->get();

        return view('propietarios.edit', compact('propietario', 'veterinarios'));
    }

    /**
     * Actualizar un propietario existente.
     * El DNI no se puede modificar porque es la clave primaria y otras
     * tablas (mascotas) lo usan como referencia.
     */
    public function update(PropietarioRequest $request, Propietario $propietario)
    {
        $validated = $request->validated();

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
