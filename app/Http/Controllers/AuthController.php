<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    /**
     * Mostrar el formulario de login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesar el intento de login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'dni' => ['required', 'string', 'max:8', 'regex:/^[0-9]+$/'],
            'password' => ['required', 'string', 'max:20'],
        ], [
            'dni.required' => 'Ingresá tu DNI.',
            'password.required' => 'Ingresá tu contraseña.',
            'dni.max' => 'El DNI puede tener como máximo 8 caracteres.',
            'dni.regex' => 'El DNI solo puede tener números.',
            'password.max' => 'La contraseña puede tener como máximo 20 caracteres.',
        ]);

        // Límite de 5 intentos fallidos por minuto para el mismo DNI + IP,
        // para frenar ataques de fuerza bruta.
        $claveIntentos = 'login|' . $request->input('dni') . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($claveIntentos, 5)) {
            $segundos = RateLimiter::availableIn($claveIntentos);

            return back()->withErrors([
                'dni' => "Demasiados intentos fallidos. Probá de nuevo en {$segundos} segundos.",
            ])->onlyInput('dni');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($claveIntentos);
            $request->session()->regenerate();
            // Siempre se entra con el rol real (el veterinario entra como veterinario).
            $request->session()->forget(\App\Models\User::SESION_VISTA_ESTUDIANTE);

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($claveIntentos, 60);

        return back()->withErrors([
            'dni' => 'El DNI o la contraseña son incorrectos.',
        ])->onlyInput('dni');
    }

    /**
     * Cerrar sesión.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
