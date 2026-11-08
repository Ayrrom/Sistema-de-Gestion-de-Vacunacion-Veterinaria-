<?php

namespace Database\Seeders;

use App\Actions\CrearUsuario;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Crea el veterinario inicial si todavía no existe. No hay rol
     * administrador: el veterinario administra el sistema y desde "Equipo"
     * da de alta al resto. El DNI y el nombre se pueden definir con las
     * variables VETERINARIO_DNI y VETERINARIO_NAME. La contraseña inicial es
     * el DNI y se pide cambiarla en el primer ingreso.
     */
    public function run(): void
    {
        $dni = (string) env('VETERINARIO_DNI', '00000000');

        if (User::where('dni', $dni)->exists()) {
            return;
        }

        app(CrearUsuario::class)->ejecutar([
            'name' => env('VETERINARIO_NAME', 'Veterinario'),
            'dni' => $dni,
            'rol' => User::ROL_VETERINARIO,
        ]);
    }
}
