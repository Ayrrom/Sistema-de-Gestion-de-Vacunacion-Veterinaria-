<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * "Mi perfil": datos del usuario logueado y, para el veterinario, el cambio
 * entre su vista de veterinario y la de estudiante.
 */
class PerfilController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load(['veterinario.estudiantes', 'estudiante.veterinario']);

        return view('perfil.show', compact('user'));
    }

    /**
     * Alterna la vista del veterinario entre "Veterinario" y "Estudiante".
     * Solo cambia la sesión actual: el rol guardado en la base no se toca.
     */
    public function cambiarVista(Request $request)
    {
        $user = $request->user();

        if (! $user->puedeCambiarVista()) {
            abort(403, 'Solo un veterinario puede cambiar de vista.');
        }

        if ($user->enVistaEstudiante()) {
            $request->session()->forget(User::SESION_VISTA_ESTUDIANTE);
            $mensaje = 'Volviste a tu rol de Veterinario.';
        } else {
            $request->session()->put(User::SESION_VISTA_ESTUDIANTE, true);
            $mensaje = 'Ahora estás viendo el sistema como Estudiante.';
        }

        return redirect()->route('dashboard')->with('success', $mensaje);
    }
}
