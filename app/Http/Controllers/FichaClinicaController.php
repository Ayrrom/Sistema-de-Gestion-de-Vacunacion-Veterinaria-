<?php

namespace App\Http\Controllers;

use App\Models\FichaClinica;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FichaClinicaController extends Controller
{
    /**
     * Listado completo del historial de consultas de una mascota.
     */
    public function historial(Mascota $mascota)
    {
        $mascota->load('propietario');
        $fichas = $mascota->fichasClinicas()->orderByDesc('fecha_consulta')->get();

        return view('fichas.historial', compact('mascota', 'fichas'));
    }

    /**
     * Descargar el historial completo de una mascota en PDF.
     */
    public function descargarPdf(Mascota $mascota)
    {
        $mascota->load('propietario');
        $fichas = $mascota->fichasClinicas()->orderByDesc('fecha_consulta')->get();

        $pdf = Pdf::loadView('fichas.historial-pdf', compact('mascota', 'fichas'));

        $nombreArchivo = 'historia-clinica-' . \Illuminate\Support\Str::slug($mascota->nombre) . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    /**
     * Formulario de nueva consulta para una mascota puntual.
     */
    public function create(Mascota $mascota)
    {
        return view('fichas.create', compact('mascota'));
    }

    /**
     * Guardar una consulta nueva.
     */
    public function store(Request $request, Mascota $mascota)
    {
        $validated = $this->validarDatos($request);
        $validated['mascota_id'] = $mascota->id;
        $validated['esquema_completo'] = $request->boolean('esquema_completo');
        $validated['hidratacion_normal'] = $request->boolean('hidratacion_normal');

        FichaClinica::create($validated);

        return redirect()->route('mascotas.show', $mascota)
            ->with('success', 'Ficha clínica registrada correctamente.');
    }

    /**
     * Ver el detalle de una consulta puntual.
     */
    public function show(FichaClinica $ficha)
    {
        $mascota = $ficha->mascota;

        return view('fichas.show', compact('ficha', 'mascota'));
    }

    /**
     * Formulario de edición de una consulta.
     */
    public function edit(FichaClinica $ficha)
    {
        $mascota = $ficha->mascota;

        return view('fichas.edit', compact('ficha', 'mascota'));
    }

    /**
     * Actualizar una consulta existente.
     */
    public function update(Request $request, FichaClinica $ficha)
    {
        $validated = $this->validarDatos($request);
        $validated['esquema_completo'] = $request->boolean('esquema_completo');
        $validated['hidratacion_normal'] = $request->boolean('hidratacion_normal');

        $ficha->update($validated);

        return redirect()->route('mascotas.show', $ficha->mascota)
            ->with('success', 'Ficha clínica actualizada correctamente.');
    }

    /**
     * Eliminar una consulta.
     */
    public function destroy(FichaClinica $ficha)
    {
        $mascota = $ficha->mascota;
        $ficha->delete();

        return redirect()->route('mascotas.show', $mascota)
            ->with('success', 'Ficha clínica eliminada correctamente.');
    }

    /**
     * Reglas de validación compartidas entre store y update.
     */
    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'fecha_consulta' => ['required', 'date'],
            'veterinario' => ['nullable', 'string', 'max:255'],
            'motivo_consulta' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'in:Saludable,En tratamiento,Revisión'],

            'vacunas_aplicadas' => ['nullable', 'string'],

            'fecha_desparasitacion' => ['nullable', 'date'],
            'droga_desparasitacion' => ['nullable', 'string', 'max:255'],
            'producto_desparasitacion' => ['nullable', 'string', 'max:255'],

            'fc' => ['nullable', 'integer', 'min:0'],
            'fr' => ['nullable', 'integer', 'min:0'],
            'temperatura' => ['nullable', 'numeric'],
            'pulso' => ['nullable', 'string', 'max:100'],
            'tllc' => ['nullable', 'string', 'max:100'],
            'relacion_entorno' => ['nullable', 'string', 'max:255'],

            'grado_deshidratacion' => ['nullable', 'string', 'in:1-5%,6-7%,8-9%,+10%'],

            'piel_anexos' => ['nullable', 'string', 'in:N,A'],
            'ojos' => ['nullable', 'string', 'in:N,A'],
            'oidos' => ['nullable', 'string', 'in:N,A'],
            'ganglios_linfaticos' => ['nullable', 'string', 'in:N,A'],
            'conjuntivas_mucosas' => ['nullable', 'string', 'in:N,A'],
            'sistema_cardiaco' => ['nullable', 'string', 'in:N,A'],
            'sistema_respiratorio' => ['nullable', 'string', 'in:N,A'],
            'sistema_digestivo' => ['nullable', 'string', 'in:N,A'],
            'sistema_urinario' => ['nullable', 'string', 'in:N,A'],
            'sistema_nervioso' => ['nullable', 'string', 'in:N,A'],
            'sistema_locomotor' => ['nullable', 'string', 'in:N,A'],

            'condicion_corporal' => ['nullable', 'integer', 'min:1', 'max:5'],
        ], [
            'fecha_consulta.required' => 'Ingresá la fecha de la consulta.',
            'motivo_consulta.required' => 'Ingresá el motivo de la consulta.',
            'estado.required' => 'Seleccioná el estado del paciente.',
        ]);
    }
}
