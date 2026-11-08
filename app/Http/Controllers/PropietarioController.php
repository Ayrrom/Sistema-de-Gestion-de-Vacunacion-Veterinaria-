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
     * Listado de propietarios, con búsqueda por DNI, nombre o apellido.
     * Si lo buscado es exactamente el DNI de un propietario, se va directo
     * a su perfil.
     */
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('buscar', ''));

        if ($busqueda !== '' && ($propietario = Propietario::find($busqueda))) {
            return redirect()->route('propietarios.show', $propietario);
        }

        $propietarios = Propietario::with('veterinario')
            ->withCount('mascotas')
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('dni', 'like', "%{$busqueda}%")
                        ->orWhere('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('apellido', 'like', "%{$busqueda}%");
                });
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('propietarios.index', compact('propietarios', 'busqueda'));
    }

    /**
     * Sugerencias para el autocompletado del DNI: hasta 10 propietarios
     * cuyo DNI empieza con lo que se lleva escrito. Devuelve JSON.
     */
    public function sugerencias(Request $request)
    {
        $inicio = preg_replace('/\D/', '', (string) $request->query('dni', ''));

        if ($inicio === '') {
            return response()->json([]);
        }

        $propietarios = Propietario::where('dni', 'like', substr($inicio, 0, 8) . '%')
            ->orderBy('dni')
            ->limit(10)
            ->get(['dni', 'nombre', 'apellido']);

        return response()->json($propietarios->map(fn ($p) => [
            'dni' => $p->dni,
            'nombre' => "{$p->nombre} {$p->apellido}",
        ]));
    }

    /**
     * Perfil del propietario con sus mascotas.
     */
    public function show(Propietario $propietario)
    {
        $propietario->load(['veterinario', 'mascotas' => function ($query) {
            $query->with('ultimaFicha')->orderBy('nombre');
        }]);

        return view('propietarios.show', compact('propietario'));
    }

    /**
     * Formulario de alta.
     */
    public function create()
    {
        $veterinarios = Veterinario::activos()->orderBy('nombre')->get();

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
        // Activos, más el que ya lo atiende aunque se haya dado de baja.
        $veterinarios = Veterinario::activos()
            ->when($propietario->veterinario_id, fn ($q) => $q->orWhere('id', $propietario->veterinario_id))
            ->orderBy('nombre')
            ->get();

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
