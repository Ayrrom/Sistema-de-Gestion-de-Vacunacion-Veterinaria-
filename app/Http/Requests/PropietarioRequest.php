<?php

namespace App\Http\Requests;

use App\Support\Campos;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación del alta y la edición de propietarios. Los tipos y máximos de
 * cada campo salen de App\Support\Campos (los mismos que usa el formulario).
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
            'nombre' => Campos::reglas('nombre_propietario', true),
            'apellido' => Campos::reglas('apellido', true),
            'localidad' => Campos::reglas('localidad', true),
            'telefono' => Campos::reglas('telefono', true),
            'calle' => Campos::reglas('calle', true),
            'altura' => Campos::reglas('altura', true),
            'veterinario_id' => ['nullable', 'exists:veterinarios,id'],
        ];

        // El DNI solo se carga en el alta: es la clave que usan las mascotas
        // y no se puede modificar después.
        if ($this->isMethod('post')) {
            $rules['dni'] = ['required', 'string', 'max:8', 'regex:/^[0-9]+$/', 'unique:propietarios,dni'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return array_merge(
            Campos::mensajes('nombre_propietario', 'El nombre', 'nombre'),
            Campos::mensajes('apellido', 'El apellido'),
            Campos::mensajes('localidad', 'La localidad'),
            Campos::mensajes('telefono', 'El teléfono'),
            Campos::mensajes('calle', 'La calle'),
            Campos::mensajes('altura', 'La altura'),
            [
                'dni.required' => 'Ingresá el DNI del propietario.',
                'dni.unique' => 'Ya existe un propietario registrado con ese DNI.',
                'dni.max' => 'El DNI puede tener como máximo 8 caracteres.',
                'dni.regex' => 'El DNI solo puede tener números.',
                'nombre.required' => 'Ingresá el nombre.',
                'apellido.required' => 'Ingresá el apellido.',
                'localidad.required' => 'Ingresá la localidad.',
                'telefono.required' => 'Ingresá un teléfono de contacto.',
                'calle.required' => 'Ingresá la calle.',
                'altura.required' => 'Ingresá la altura.',
            ],
        );
    }
}
