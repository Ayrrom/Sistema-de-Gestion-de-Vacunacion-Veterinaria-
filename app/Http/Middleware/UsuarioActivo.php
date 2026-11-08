<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si el usuario fue dado de baja mientras tenía la sesión abierta, se la
 * cierra en el próximo clic.
 */
class UsuarioActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->activo === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['dni' => 'Tu usuario fue dado de baja. Consultá con un veterinario o un administrador.']);
        }

        return $next($request);
    }
}
