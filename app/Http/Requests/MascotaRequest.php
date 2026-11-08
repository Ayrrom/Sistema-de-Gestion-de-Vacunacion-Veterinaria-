<?php

namespace App\Http\Requests;

use App\Support\Campos;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación del alta y la edición de pacientes. Los tipos y máximos de
 * cada campo salen de App\Support\Campos (los mismos que usa el formulario).
 */
class MascotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el acceso ya lo controlan los middlewares de las rutas
    }

    /** Opciones válidas de los selectores del formulario. */
    public const ESPECIES = ['Perro', 'Gato'];
    public const SEXOS = ['Macho', 'Hembra'];

    public function rules(): array
    {
        $rules = [
            'nombre' => Campos::reglas('nombre_mascota', true),
            'especie' => ['required', 'string', 'in:' . implode(',', self::ESPECIES)],
            'raza' => Campos::reglas('raza', true),
            'sexo' => ['required', 'string', 'in:' . implode(',', self::SEXOS)],
            'peso' => Campos::reglas('peso', true),
            'edad' => Campos::reglas('edad', true),
            'tipo_pelaje' => Campos::reglas('tipo_pelaje', true),
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'color' => Campos::reglas('color'),
            'procedencia' => Campos::reglas('procedencia'),
            'senas_particulares' => Campos::reglas('senas_particulares'),
        ];

        // El propietario solo se elige al dar de alta; en la edición no se cambia.
        if ($this->isMethod('post')) {
            $rules['propietario_dni'] = ['required', 'string', 'exists:propietarios,dni'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return array_merge(
            Campos::mensajes('nombre_mascota', 'El nombre', 'nombre'),
            Campos::mensajes('raza', 'La raza'),
            Campos::mensajes('peso', 'El peso (kg)'),
            Campos::mensajes('edad', 'La edad (años)'),
            Campos::mensajes('tipo_pelaje', 'El tipo de pelaje'),
            Campos::mensajes('color', 'El color'),
            Campos::mensajes('procedencia', 'La procedencia'),
            Campos::mensajes('senas_particulares', 'Las señas particulares'),
            [
                'propietario_dni.exists' => 'El propietario seleccionado no es válido.',
                'nombre.required' => 'Ingresá el nombre de la mascota.',
                'especie.required' => 'Seleccioná la especie.',
                'especie.in' => 'La especie seleccionada no es válida.',
                'raza.required' => 'Ingresá la raza.',
                'sexo.required' => 'Seleccioná el sexo.',
                'sexo.in' => 'El sexo seleccionado no es válido.',
                'peso.required' => 'Ingresá el peso.',
                'peso.numeric' => 'El peso tiene que ser un número.',
                'edad.required' => 'Ingresá la edad.',
                'edad.integer' => 'La edad tiene que ser un número entero.',
                'tipo_pelaje.required' => 'Ingresá el tipo de pelaje.',
                'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            ],
        );
    }

    /** Datos validados listos para guardar (incluye el checkbox "castrado"). */
    public function datos(): array
    {
        return $this->validated() + ['castrado' => $this->boolean('castrado')];
    }
}
