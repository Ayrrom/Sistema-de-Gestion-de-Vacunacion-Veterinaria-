<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Propietario;
use App\Http\Requests\MascotaRequest;
use Illuminate\Http\Request;

class MascotaController extends Controller
{
    /** Especies que se pueden elegir (las mismas del formulario del paciente). */
    private const ESPECIES = ['Perro', 'Gato'];

    /**
     * Listado de mascotas (pacientes).
     */
    public function index(Request $request)
    {
        $filtros = $this->filtrosDelListado($request);

        $mascotas = Mascota::with(['propietario', 'ultimaFicha'])
            // DNI del propietario: los que empiezan con lo escrito
            ->when($filtros['dni'] !== '', fn ($q) => $q->where('propietario_dni', 'like', $filtros['dni'] . '%'))
            ->when($filtros['especie'] !== '', fn ($q) => $q->where('especie', $filtros['especie']))
            // Fecha: pacientes con al menos una consulta dentro del rango
            ->when($filtros['desde'] || $filtros['hasta'], function ($q) use ($filtros) {
                $q->whereHas('fichasClinicas', function ($fichas) use ($filtros) {
                    $fichas->when($filtros['desde'], fn ($f) => $f->whereDate('fecha_consulta', '>=', $filtros['desde']))
                        ->when($filtros['hasta'], fn ($f) => $f->whereDate('fecha_consulta', '<=', $filtros['hasta']));
                });
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $especies = self::ESPECIES;
        $hayFiltros = $filtros['dni'] !== '' || $filtros['especie'] !== '' || $filtros['desde'] || $filtros['hasta'];

        return view('mascotas.index', compact('mascotas', 'filtros', 'especies', 'hayFiltros'));
    }

    /**
     * Lee los filtros del listado. Los valores inválidos se ignoran en vez
     * de dar error, porque vienen de la barra de direcciones.
     */
    private function filtrosDelListado(Request $request): array
    {
        $fecha = function ($valor) {
            $valor = (string) $valor;
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) && strtotime($valor) ? $valor : null;
        };

        $desde = $fecha($request->query('desde'));
        $hasta = $fecha($request->query('hasta'));

        // Si se cargaron al revés, se invierten.
        if ($desde && $hasta && $desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $especie = (string) $request->query('especie', '');

        return [
            'dni' => substr(preg_replace('/\D/', '', (string) $request->query('dni', '')), 0, 8),
            'especie' => in_array($especie, self::ESPECIES, true) ? $especie : '',
            'desde' => $desde,
            'hasta' => $hasta,
        ];
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
        // No se borra una mascota con historia clínica: se perderían sus fichas
        // y además la clave foránea de fichas_clinicas lo impide.
        if ($mascota->fichasClinicas()->exists()) {
            return back()->with('error', 'No se puede eliminar: el paciente tiene fichas clínicas registradas.');
        }

        $mascota->delete();

        return redirect()->route('mascotas.index')
            ->with('success', 'Paciente eliminado correctamente.');
    }
}
