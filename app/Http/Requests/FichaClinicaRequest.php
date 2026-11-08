<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación del alta y la edición de fichas clínicas (antes era el método
 * privado validarDatos() de FichaClinicaController).
 */
class FichaClinicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el acceso ya lo controlan los middlewares de las rutas
    }

    public function rules(): array
    {
        return [
            'fecha_consulta' => ['required', 'date'],
            'veterinario_id' => ['nullable', 'exists:veterinarios,id'],
            'estudiantes' => ['nullable', 'array'],
            'estudiantes.*' => ['integer', 'exists:estudiantes,id'],
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
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_consulta.required' => 'Ingresá la fecha de la consulta.',
            'motivo_consulta.required' => 'Ingresá el motivo de la consulta.',
            'estado.required' => 'Seleccioná el estado del paciente.',
        ];
    }
}
