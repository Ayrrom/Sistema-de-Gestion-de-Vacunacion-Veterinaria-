<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si el usuario todavía tiene la contraseña inicial (su DNI), no lo deja
 * usar el sistema hasta que la cambie.
 */
class ObligarCambioPassword
{
    /** Rutas que se pueden usar aunque falte cambiar la contraseña. */
    private const RUTAS_PERMITIDAS = ['password.edit', 'password.update', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->debe_cambiar_password && ! $request->routeIs(self::RUTAS_PERMITIDAS)) {
            return redirect()->route('password.edit')
                ->with('error', 'Por seguridad tenés que cambiar tu contraseña inicial antes de continuar.');
        }

        return $next($request);
    }
}
