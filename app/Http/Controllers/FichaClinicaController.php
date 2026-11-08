<?php

namespace App\Http\Controllers;

use App\Http\Requests\FichaClinicaRequest;
use App\Models\Estudiante;
use App\Models\FichaClinica;
use App\Models\Mascota;
use App\Models\Veterinario;
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
        $fichas = $mascota->fichasClinicas()->with('estudiantes')->orderByDesc('fecha_consulta')->get();

        $pdf = Pdf::loadView('fichas.historial-pdf', compact('mascota', 'fichas'));

        $nombreArchivo = 'historia-clinica-' . \Illuminate\Support\Str::slug($mascota->nombre) . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    /**
     * Formulario de nueva consulta para una mascota puntual.
     */
    public function create(Mascota $mascota)
    {
        return view('fichas.create', array_merge(compact('mascota'), $this->listasDelFormulario()));
    }

    /**
     * Guardar una consulta nueva.
     */
    public function store(FichaClinicaRequest $request, Mascota $mascota)
    {
        $validated = $request->validated();
        $validated['mascota_id'] = $mascota->id;
        $validated = $this->completarVeterinario($validated);
        $validated['esquema_completo'] = $request->boolean('esquema_completo');
        $validated['hidratacion_normal'] = $request->boolean('hidratacion_normal');

        $ficha = FichaClinica::create($validated);
        $this->guardarEstudiantes($request, $ficha);

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

        $ficha->load('estudiantes');

        return view('fichas.edit', array_merge(compact('ficha', 'mascota'), $this->listasDelFormulario($ficha)));
    }

    /**
     * Actualizar una consulta existente.
     */
    public function update(FichaClinicaRequest $request, FichaClinica $ficha)
    {
        $validated = $this->completarVeterinario($request->validated());

        // Si se quitó el veterinario asignado, se borra también su nombre guardado.
        if ($ficha->veterinario_id && empty($validated['veterinario_id'])) {
            $validated['veterinario'] = null;
        }

        $validated['esquema_completo'] = $request->boolean('esquema_completo');
        $validated['hidratacion_normal'] = $request->boolean('hidratacion_normal');

        $ficha->update($validated);
        $this->guardarEstudiantes($request, $ficha);

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
     * Veterinarios y estudiantes para los selectores del formulario.
     */
    private function listasDelFormulario(?FichaClinica $ficha = null): array
    {
        return [
            // Solo los activos, más los que ya estaban asignados a esta ficha
            // (aunque después se hayan dado de baja), para no perder el dato al editar.
            'veterinarios' => Veterinario::activos()
                ->when($ficha?->veterinario_id, fn ($q) => $q->orWhere('id', $ficha->veterinario_id))
                ->orderBy('nombre')->get(),
            'estudiantes' => Estudiante::activos()
                ->when($ficha, fn ($q) => $q->orWhereIn('id', $ficha->estudiantes()->pluck('estudiantes.id')))
                ->orderBy('nombre')->get(),
        ];
    }

    /**
     * Si se eligió un veterinario, guarda también su nombre en la columna de
     * texto "veterinario", que es la que muestran el historial y el PDF y la
     * que tienen las fichas cargadas antes de este cambio.
     */
    private function completarVeterinario(array $validated): array
    {
        if (! empty($validated['veterinario_id'])) {
            $validated['veterinario'] = Veterinario::find($validated['veterinario_id'])->nombre;
        }

        unset($validated['estudiantes']);

        return $validated;
    }

    /**
     * Registra qué estudiantes participaron de la consulta, con la fecha de
     * la consulta como fecha_atencion (relación M:N del DER).
     */
    private function guardarEstudiantes(Request $request, FichaClinica $ficha): void
    {
        $ids = $request->input('estudiantes', []);
        $fecha = $ficha->fecha_consulta->format('Y-m-d');

        $ficha->estudiantes()->sync(
            collect($ids)->mapWithKeys(fn ($id) => [(int) $id => ['fecha_atencion' => $fecha]])->all()
        );
    }
}
