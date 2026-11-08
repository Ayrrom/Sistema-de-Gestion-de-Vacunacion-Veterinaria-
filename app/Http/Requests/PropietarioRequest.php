<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación del alta y la edición de propietarios (antes estaba copiada en
 * PropietarioController@store y @update).
 */
class PropietarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el acceso ya lo controlan los middlewares de las rutas
    }

    public function rules(): array
    {
        $rules = [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'localidad' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:50'],
            'calle' => ['required', 'string', 'max:255'],
            'altura' => ['required', 'string', 'max:20'],
            'veterinario_id' => ['nullable', 'exists:veterinarios,id'],
        ];

        // El DNI solo se carga en el alta: es la clave que usan las mascotas
        // y no se puede modificar después.
        if ($this->isMethod('post')) {
            $rules['dni'] = ['required', 'string', 'max:8', 'unique:propietarios,dni'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'Ingresá el DNI del propietario.',
            'dni.unique' => 'Ya existe un propietario registrado con ese DNI.',
            'dni.max' => 'El DNI puede tener como máximo 8 caracteres.',
            'nombre.required' => 'Ingresá el nombre.',
            'apellido.required' => 'Ingresá el apellido.',
            'localidad.required' => 'Ingresá la localidad.',
            'telefono.required' => 'Ingresá un teléfono de contacto.',
            'calle.required' => 'Ingresá la calle.',
            'altura.required' => 'Ingresá la altura.',
        ];
    }
}
