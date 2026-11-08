<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - propietarios.veterinario_id: relación "atiende" del DER.
     * - fichas_clinicas.veterinario_id: veterinario responsable de la consulta.
     *   La columna de texto fichas_clinicas.veterinario se mantiene: guarda el
     *   nombre al momento de la consulta y conserva los datos ya cargados.
     * Ambas son opcionales para no romper los registros existentes.
     */
    public function up(): void
    {
        Schema::table('propietarios', function (Blueprint $table) {
            $table->foreignId('veterinario_id')->nullable()->after('localidad')->constrained('veterinarios')->nullOnDelete();
        });

        Schema::table('fichas_clinicas', function (Blueprint $table) {
            $table->foreignId('veterinario_id')->nullable()->after('veterinario')->constrained('veterinarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fichas_clinicas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('veterinario_id');
        });

        Schema::table('propietarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('veterinario_id');
        });
    }
};
