<?php

namespace App\Http\Controllers;

use App\Actions\CrearUsuario;
use App\Models\User;
use App\Models\Veterinario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * "Equipo": el panel de administración del veterinario. Desde acá da de
 * alta y de baja a veterinarios y estudiantes y les restablece la contraseña.
 * (No hay rol administrador: administra el veterinario.)
 *
 * La baja no borra al usuario: lo desactiva (no puede ingresar ni aparece en
 * los selectores) y conserva todo su historial. Se puede volver a dar de alta.
 */
class EquipoController extends Controller
{
    public function index()
    {
        $miembros = User::with(['veterinario.estudiantes', 'estudiante.veterinario'])
            ->orderByDesc('activo')
            ->orderBy('name')
            ->get();

        return view('equipo.index', [
            'veterinarios' => $miembros->where('rol', User::ROL_VETERINARIO)->values(),
            'estudiantes' => $miembros->where('rol', User::ROL_ESTUDIANTE)->values(),
        ]);
    }

    public function create(Request $request)
    {
        $rol = array_key_exists((string) $request->query('rol'), User::ROLES) ? $request->query('rol') : User::ROL_ESTUDIANTE;
        $veterinarios = Veterinario::activos()->orderBy('nombre')->get();
        $miVeterinario = $request->user()->veterinario;

        return view('equipo.create', compact('rol', 'veterinarios', 'miVeterinario'));
    }

    public function store(Request $request, CrearUsuario $crearUsuario)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dni' => CrearUsuario::REGLAS_DNI,
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
            'catedra' => ['nullable', 'string', 'max:255'],
            'veterinario_id' => ['nullable', Rule::exists('veterinarios', 'id')],
        ], CrearUsuario::MENSAJES);

        $user = $crearUsuario->ejecutar($validated);

        return redirect()->route('equipo.index')
            ->with('success', "{$user->name} fue dado de alta como {$user->nombre_rol_real}. Su contraseña inicial es su DNI y deberá cambiarla al ingresar.");
    }

    /** Dar de baja: desactiva al usuario sin borrar su historial. */
    public function baja(Request $request, User $usuario)
    {
        $this->noASiMismo($request->user(), $usuario);

        $usuario->update(['activo' => false]);

        $mensaje = "{$usuario->name} fue dado de baja. Ya no puede ingresar al sistema; su historial se conserva.";
        $aCargo = $usuario->veterinario?->estudiantes()->activos()->count() ?? 0;
        if ($aCargo > 0) {
            $mensaje .= $aCargo === 1
                ? ' Tenía 1 estudiante a cargo, que sigue asignado a su nombre.'
                : " Tenía {$aCargo} estudiantes a cargo, que siguen asignados a su nombre.";
        }

        return back()->with('success', $mensaje);
    }

    /** Volver a dar de alta a alguien que estaba dado de baja. */
    public function alta(Request $request, User $usuario)
    {
        $this->noASiMismo($request->user(), $usuario);

        $usuario->update(['activo' => true]);

        return back()->with('success', "{$usuario->name} fue dado de alta nuevamente y ya puede ingresar.");
    }

    /** Restablece la contraseña al DNI y obliga a cambiarla en el próximo ingreso. */
    public function restablecerPassword(Request $request, User $usuario)
    {
        $this->noASiMismo($request->user(), $usuario);

        $usuario->update([
            'password' => $usuario->dni,
            'debe_cambiar_password' => true,
        ]);

        return back()->with('success', "La contraseña de {$usuario->name} se restableció a su DNI. Va a tener que cambiarla al ingresar.");
    }

    /**
     * Nadie puede darse de baja ni restablecerse la contraseña a sí mismo
     * (para su propia contraseña está "Cambiar contraseña").
     */
    private function noASiMismo(User $actor, User $usuario): void
    {
        if ($actor->id === $usuario->id) {
            abort(403, 'No podés hacer esta acción sobre tu propio usuario.');
        }
    }
}
