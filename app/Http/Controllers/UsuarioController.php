<?php

namespace App\Http\Controllers;

use App\Actions\CrearUsuario;
use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Http\Request;
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
            ->orderByDesc('activo')
            ->orderBy('rol')
            ->orderBy('name')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $roles = User::ROLES;
        $veterinarios = Veterinario::activos()->orderBy('nombre')->get();

        return view('usuarios.create', compact('roles', 'veterinarios'));
    }

    public function store(Request $request, CrearUsuario $crearUsuario)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dni' => CrearUsuario::REGLAS_DNI,
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
            'catedra' => ['nullable', 'string', 'max:255'],
            'veterinario_id' => ['nullable', 'exists:veterinarios,id'],
        ], CrearUsuario::MENSAJES);

        $user = $crearUsuario->ejecutar($validated);

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$user->name} creado como {$user->nombre_rol_real}. Su contraseña inicial es su DNI y deberá cambiarla al ingresar.");
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
