<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite dar de baja a un usuario sin borrarlo: no puede ingresar ni
     * aparece en los selectores, pero su historial (fichas que atendió,
     * estudiantes a cargo, etc.) se conserva. Todos los existentes quedan
     * activos.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('rol');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
