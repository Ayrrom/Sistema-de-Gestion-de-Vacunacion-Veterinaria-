<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación del alta y la edición de pacientes (antes estaba copiada en
 * MascotaController@store y @update).
 */
class MascotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el acceso ya lo controlan los middlewares de las rutas
    }

    public function rules(): array
    {
        $rules = [
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
        ];

        // El propietario solo se elige al dar de alta; en la edición no se cambia.
        if ($this->isMethod('post')) {
            $rules['propietario_dni'] = ['required', 'string', 'exists:propietarios,dni'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
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
        ];
    }

    /** Datos validados listos para guardar (incluye el checkbox "castrado"). */
    public function datos(): array
    {
        return $this->validated() + ['castrado' => $this->boolean('castrado')];
    }
}
