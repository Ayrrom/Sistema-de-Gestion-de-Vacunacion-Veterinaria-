<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            'dni' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'dni.required' => 'Ingresá tu DNI.',
            'password.required' => 'Ingresá tu contraseña.',
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

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($claveIntentos, 60);

        return back()->withErrors([
            'dni' => 'El DNI o la contraseña son incorrectos.',
        ])->onlyInput('dni');
    }

    /**
     * Mostrar el formulario de registro.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Dar de alta un nuevo usuario (solo desde una sesión iniciada).
     * La contraseña inicial es el DNI y el usuario queda obligado a
     * cambiarla la primera vez que ingrese.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'string', 'max:20', 'unique:users,dni'],
        ], [
            'name.required' => 'Ingresá el nombre.',
            'dni.required' => 'Ingresá el DNI.',
            'dni.unique' => 'Ya existe un usuario registrado con ese DNI.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'dni' => $validated['dni'],
            'password' => $validated['dni'], // contraseña inicial = DNI
            'debe_cambiar_password' => true,
        ]);

        return redirect()->route('register')
            ->with('success', "Usuario {$user->name} creado. Su contraseña inicial es su DNI y deberá cambiarla al ingresar.");
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
