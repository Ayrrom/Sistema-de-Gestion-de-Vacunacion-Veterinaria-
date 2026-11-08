<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Se elimina el rol "admin": el veterinario pasa a administrar el sistema.
     * Cada usuario admin se convierte en veterinario y se le crea (o vincula)
     * su registro en la tabla veterinarios, para que aparezca como
     * veterinario en las fichas, en Equipo, etc. Nadie pierde el acceso.
     */
    public function up(): void
    {
        $admins = DB::table('users')->where('rol', 'admin')->get();

        foreach ($admins as $user) {
            DB::table('users')->where('id', $user->id)->update(['rol' => 'veterinario', 'updated_at' => now()]);

            $yaTienePerfil = DB::table('veterinarios')->where('user_id', $user->id)->exists();
            if ($yaTienePerfil) {
                continue;
            }

            // Si ya había un veterinario cargado con ese DNI y sin usuario, se vincula.
            $mismoDni = DB::table('veterinarios')->where('dni', $user->dni)->whereNull('user_id')->first();
            if ($mismoDni) {
                DB::table('veterinarios')->where('id', $mismoDni->id)->update(['user_id' => $user->id, 'updated_at' => now()]);
                continue;
            }

            // Si el DNI ya lo usa otro veterinario, no se puede duplicar: queda
            // como veterinario sin ficha en la tabla (igual puede usar todo).
            if (DB::table('veterinarios')->where('dni', $user->dni)->exists()) {
                continue;
            }

            DB::table('veterinarios')->insert([
                'user_id' => $user->id,
                'dni' => $user->dni,
                'nombre' => $user->name,
                'catedra' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * No se puede saber con certeza quiénes eran administradores antes, así
     * que no se revierte (los usuarios siguen como veterinarios).
     */
    public function down(): void
    {
        //
    }
};
