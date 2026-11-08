<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Gestión de usuarios (solo administradores).
 * Al crear un veterinario o un estudiante se crea también su registro en
 * la tabla correspondiente del DER, vinculado al usuario.
 */
class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['veterinario', 'estudiante.veterinario'])
            ->orderBy('rol')
            ->orderBy('name')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $roles = User::ROLES;
        $veterinarios = Veterinario::orderBy('nombre')->get();

        return view('usuarios.create', compact('roles', 'veterinarios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'string', 'max:20', 'unique:users,dni', 'unique:veterinarios,dni', 'unique:estudiantes,dni'],
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
            'catedra' => ['nullable', 'string', 'max:255'],
            'veterinario_id' => ['nullable', 'exists:veterinarios,id'],
        ], [
            'name.required' => 'Ingresá el nombre.',
            'dni.required' => 'Ingresá el DNI.',
            'dni.unique' => 'Ya existe una persona registrada con ese DNI.',
            'rol.required' => 'Seleccioná el rol.',
            'rol.in' => 'El rol seleccionado no es válido.',
            'veterinario_id.exists' => 'El veterinario seleccionado no es válido.',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'dni' => $validated['dni'],
                'rol' => $validated['rol'],
                'password' => $validated['dni'], // contraseña inicial = DNI
                'debe_cambiar_password' => true,
            ]);

            if ($user->rol === User::ROL_VETERINARIO) {
                Veterinario::create([
                    'user_id' => $user->id,
                    'dni' => $user->dni,
                    'nombre' => $user->name,
                    'catedra' => $validated['catedra'] ?? null,
                ]);
            }

            if ($user->rol === User::ROL_ESTUDIANTE) {
                Estudiante::create([
                    'user_id' => $user->id,
                    'dni' => $user->dni,
                    'nombre' => $user->name,
                    'veterinario_id' => $validated['veterinario_id'] ?? null,
                ]);
            }

            return $user;
        });

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$user->name} creado como {$user->nombre_rol}. Su contraseña inicial es su DNI y deberá cambiarla al ingresar.");
    }

    /**
     * Restablece la contraseña al DNI y obliga a cambiarla en el próximo ingreso.
     */
    public function restablecerPassword(User $usuario)
    {
        $usuario->update([
            'password' => $usuario->dni,
            'debe_cambiar_password' => true,
        ]);

        return back()->with('success', "La contraseña de {$usuario->name} se restableció a su DNI.");
    }
}
