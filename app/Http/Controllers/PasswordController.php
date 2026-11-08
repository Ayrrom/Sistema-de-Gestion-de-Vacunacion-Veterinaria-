<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Formulario para cambiar la contraseña.
     */
    public function edit()
    {
        return view('auth.cambiar-password');
    }

    /**
     * Guardar la nueva contraseña.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password_actual' => ['required', 'max:20', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8), 'max:20', 'not_in:' . $user->dni],
        ], [
            'password_actual.required' => 'Ingresá tu contraseña actual.',
            'password_actual.current_password' => 'La contraseña actual no es correcta.',
            'password.required' => 'Ingresá la nueva contraseña.',
            'password.confirmed' => 'Las contraseñas nuevas no coinciden.',
            'password.min' => 'La nueva contraseña tiene que tener al menos 8 caracteres.',
            'password.max' => 'La nueva contraseña puede tener como máximo 20 caracteres.',
            'password_actual.max' => 'La contraseña puede tener como máximo 20 caracteres.',
            'password.not_in' => 'La nueva contraseña no puede ser tu DNI.',
        ]);

        $user->update([
            'password' => $request->input('password'),
            'debe_cambiar_password' => false,
        ]);

        // Renueva el ID de la sesión después de cambiar la contraseña.
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Contraseña actualizada correctamente.');
    }
}
