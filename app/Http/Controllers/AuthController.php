<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

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
     * Procesar el registro de un nuevo usuario.
     * La contraseña se asigna automáticamente igual al DNI.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'string', 'max:20', 'unique:users,dni'],
        ], [
            'name.required' => 'Ingresá tu nombre.',
            'dni.required' => 'Ingresá tu DNI.',
            'dni.unique' => 'Ya existe un usuario registrado con ese DNI.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'dni' => $validated['dni'],
            'password' => $validated['dni'], // la contraseña inicial es el propio DNI
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
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
