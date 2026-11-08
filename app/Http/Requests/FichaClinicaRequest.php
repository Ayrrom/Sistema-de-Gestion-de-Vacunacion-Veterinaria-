<?php

namespace App\Http\Requests;

use App\Support\Campos;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación del alta y la edición de fichas clínicas. Los tipos y máximos
 * de los campos de texto/número salen de App\Support\Campos (los mismos que
 * usa el formulario).
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
            'fecha_consulta' => ['required', 'date', 'after_or_equal:2000-01-01', 'before_or_equal:today'],
            'veterinario_id' => ['nullable', 'exists:veterinarios,id'],
            'estudiantes' => ['nullable', 'array'],
            'estudiantes.*' => ['integer', 'exists:estudiantes,id'],
            'motivo_consulta' => Campos::reglas('motivo_consulta', true),
            'estado' => ['required', 'string', 'in:Saludable,En tratamiento,Revisión'],

            'vacunas_aplicadas' => Campos::reglas('vacunas_aplicadas'),

            'fecha_desparasitacion' => ['nullable', 'date', 'after_or_equal:2000-01-01', 'before_or_equal:today'],
            'droga_desparasitacion' => Campos::reglas('droga_desparasitacion'),
            'producto_desparasitacion' => Campos::reglas('producto_desparasitacion'),

            'fc' => Campos::reglas('fc'),
            'fr' => Campos::reglas('fr'),
            'temperatura' => Campos::reglas('temperatura'),
            'pulso' => Campos::reglas('pulso'),
            'tllc' => Campos::reglas('tllc'),
            'relacion_entorno' => Campos::reglas('relacion_entorno'),

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
        return array_merge(
            Campos::mensajes('motivo_consulta', 'El motivo de consulta'),
            Campos::mensajes('vacunas_aplicadas', 'El detalle de vacunas'),
            Campos::mensajes('droga_desparasitacion', 'La droga'),
            Campos::mensajes('producto_desparasitacion', 'El producto'),
            Campos::mensajes('fc', 'La FC'),
            Campos::mensajes('fr', 'La FR'),
            Campos::mensajes('temperatura', 'La temperatura (°C)'),
            Campos::mensajes('pulso', 'El pulso'),
            Campos::mensajes('tllc', 'El TLLC'),
            Campos::mensajes('relacion_entorno', 'La relación con el entorno'),
            [
                'fecha_consulta.required' => 'Ingresá la fecha de la consulta.',
                'fecha_consulta.before_or_equal' => 'La fecha de la consulta no puede ser futura.',
                'fecha_consulta.after_or_equal' => 'La fecha de la consulta no es válida.',
                'fecha_desparasitacion.before_or_equal' => 'La fecha de desparasitación no puede ser futura.',
                'fecha_desparasitacion.after_or_equal' => 'La fecha de desparasitación no es válida.',
                'motivo_consulta.required' => 'Ingresá el motivo de la consulta.',
                'estado.required' => 'Seleccioná el estado del paciente.',
            ],
        );
    }
}
