<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FichaClinicaController;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\PropietarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de invitado (sin sesión iniciada)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Rutas protegidas (con sesión iniciada)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('propietarios', PropietarioController::class)->except(['show']);

    Route::get('/mascotas', [MascotaController::class, 'index'])->name('mascotas.index');
    Route::get('/mascotas/create', [MascotaController::class, 'create'])->name('mascotas.create');
    Route::post('/mascotas', [MascotaController::class, 'store'])->name('mascotas.store');
    Route::get('/mascotas/{mascota}', [MascotaController::class, 'show'])->name('mascotas.show');
    Route::get('/mascotas/{mascota}/edit', [MascotaController::class, 'edit'])->name('mascotas.edit');
    Route::put('/mascotas/{mascota}', [MascotaController::class, 'update'])->name('mascotas.update');
    Route::delete('/mascotas/{mascota}', [MascotaController::class, 'destroy'])->name('mascotas.destroy');

    Route::get('/mascotas/{mascota}/fichas/create', [FichaClinicaController::class, 'create'])->name('fichas.create');
    Route::post('/mascotas/{mascota}/fichas', [FichaClinicaController::class, 'store'])->name('fichas.store');
    Route::get('/mascotas/{mascota}/historial', [FichaClinicaController::class, 'historial'])->name('fichas.historial');
    Route::get('/mascotas/{mascota}/historial/pdf', [FichaClinicaController::class, 'descargarPdf'])->name('fichas.historial.pdf');
    Route::get('/fichas/{ficha}', [FichaClinicaController::class, 'show'])->name('fichas.show');
    Route::get('/fichas/{ficha}/edit', [FichaClinicaController::class, 'edit'])->name('fichas.edit');
    Route::put('/fichas/{ficha}', [FichaClinicaController::class, 'update'])->name('fichas.update');
    Route::delete('/fichas/{ficha}', [FichaClinicaController::class, 'destroy'])->name('fichas.destroy');
});
