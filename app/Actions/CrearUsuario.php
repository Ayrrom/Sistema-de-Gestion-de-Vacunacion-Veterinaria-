<?php

namespace App\Actions;

use App\Models\Estudiante;
use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Support\Facades\DB;

/**
 * Da de alta a una persona en el sistema. La usan "Equipo" (veterinario) y
 * el seeder del veterinario inicial, así el alta es igual en los dos lugares.
 *
 * - Contraseña inicial = DNI, con cambio obligatorio en el primer ingreso.
 * - Si es veterinario o estudiante, crea también su registro del DER
 *   (tabla veterinarios / estudiantes) vinculado al usuario.
 */
class CrearUsuario
{
    /** Reglas del DNI, compartidas por todos los formularios de alta. */
    public const REGLAS_DNI = ['required', 'string', 'max:8', 'regex:/^[0-9]+$/', 'unique:users,dni', 'unique:veterinarios,dni', 'unique:estudiantes,dni'];

    public const MENSAJES = [
        'name.required' => 'Ingresá el nombre.',
        'dni.required' => 'Ingresá el DNI.',
        'dni.unique' => 'Ya existe una persona registrada con ese DNI.',
        'dni.max' => 'El DNI puede tener como máximo 8 caracteres.',
        'dni.regex' => 'El DNI solo puede tener números.',
        'rol.required' => 'Seleccioná el rol.',
        'rol.in' => 'El rol seleccionado no es válido.',
        'veterinario_id.exists' => 'El veterinario seleccionado no es válido.',
    ];

    /**
     * @param  array{name: string, dni: string, rol: string, catedra?: ?string, veterinario_id?: ?int}  $datos
     */
    public function ejecutar(array $datos): User
    {
        return DB::transaction(function () use ($datos) {
            $user = User::create([
                'name' => $datos['name'],
                'dni' => $datos['dni'],
                'rol' => $datos['rol'],
                'password' => $datos['dni'], // contraseña inicial = DNI
                'debe_cambiar_password' => true,
            ]);

            if ($user->rol === User::ROL_VETERINARIO) {
                Veterinario::create([
                    'user_id' => $user->id,
                    'dni' => $user->dni,
                    'nombre' => $user->name,
                    'catedra' => $datos['catedra'] ?? null,
                ]);
            }

            if ($user->rol === User::ROL_ESTUDIANTE) {
                Estudiante::create([
                    'user_id' => $user->id,
                    'dni' => $user->dni,
                    'nombre' => $user->name,
                    'veterinario_id' => $datos['veterinario_id'] ?? null,
                ]);
            }

            return $user;
        });
    }
}
