<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Crea el usuario administrador inicial si todavía no existe.
     * Como el registro público está cerrado, este usuario es el que da de
     * alta al resto. El DNI y el nombre se pueden definir con las variables
     * ADMIN_DNI y ADMIN_NAME. La contraseña inicial es el DNI y se pide
     * cambiarla en el primer ingreso.
     */
    public function run(): void
    {
        $dni = env('ADMIN_DNI', '00000000');

        User::firstOrCreate(
            ['dni' => $dni],
            [
                'name' => env('ADMIN_NAME', 'Administrador'),
                'password' => $dni,
                'debe_cambiar_password' => true,
            ]
        );
    }
}
