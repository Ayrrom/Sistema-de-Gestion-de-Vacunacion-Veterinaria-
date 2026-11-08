<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FichaClinicaController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PropietarioController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de invitado (sin sesión iniciada)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    // El límite de intentos fallidos se controla en AuthController@login.
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas protegidas (con sesión iniciada)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Cambio de contraseña (accesible aunque falte cambiar la contraseña inicial)
    Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
});

// Rutas protegidas que además exigen haber cambiado la contraseña inicial
Route::middleware(['auth', 'cambio.password'])->group(function () {
    // Gestión de usuarios: solo administradores.
    Route::middleware('rol:admin')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::put('/usuarios/{usuario}/restablecer-password', [UsuarioController::class, 'restablecerPassword'])->name('usuarios.restablecer-password');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('propietarios', PropietarioController::class)->except(['destroy']);

    Route::get('/mascotas', [MascotaController::class, 'index'])->name('mascotas.index');
    Route::get('/mascotas/create', [MascotaController::class, 'create'])->name('mascotas.create');
    Route::post('/mascotas', [MascotaController::class, 'store'])->name('mascotas.store');
    Route::get('/mascotas/{mascota}', [MascotaController::class, 'show'])->name('mascotas.show');
    Route::get('/mascotas/{mascota}/edit', [MascotaController::class, 'edit'])->name('mascotas.edit');
    Route::put('/mascotas/{mascota}', [MascotaController::class, 'update'])->name('mascotas.update');

    Route::get('/mascotas/{mascota}/fichas/create', [FichaClinicaController::class, 'create'])->name('fichas.create');
    Route::post('/mascotas/{mascota}/fichas', [FichaClinicaController::class, 'store'])->name('fichas.store');
    Route::get('/mascotas/{mascota}/historial', [FichaClinicaController::class, 'historial'])->name('fichas.historial');
    Route::get('/mascotas/{mascota}/historial/pdf', [FichaClinicaController::class, 'descargarPdf'])->name('fichas.historial.pdf');
    Route::get('/fichas/{ficha}', [FichaClinicaController::class, 'show'])->name('fichas.show');
    Route::get('/fichas/{ficha}/edit', [FichaClinicaController::class, 'edit'])->name('fichas.edit');
    Route::put('/fichas/{ficha}', [FichaClinicaController::class, 'update'])->name('fichas.update');

    // Eliminar: solo administradores y veterinarios (el estudiante no puede borrar).
    Route::middleware('rol:admin,veterinario')->group(function () {
        Route::delete('/propietarios/{propietario}', [PropietarioController::class, 'destroy'])->name('propietarios.destroy');
        Route::delete('/mascotas/{mascota}', [MascotaController::class, 'destroy'])->name('mascotas.destroy');
        Route::delete('/fichas/{ficha}', [FichaClinicaController::class, 'destroy'])->name('fichas.destroy');
    });
});
