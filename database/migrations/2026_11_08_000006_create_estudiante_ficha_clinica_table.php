<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Relación M:N ESTUDIANTE <-> FICHACLINICA del DER, con su atributo
     * fecha_atencion.
     */
    public function up(): void
    {
        Schema::create('estudiante_ficha_clinica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('ficha_clinica_id')->constrained('fichas_clinicas')->cascadeOnDelete();
            $table->date('fecha_atencion');
            $table->timestamps();

            $table->unique(['estudiante_id', 'ficha_clinica_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiante_ficha_clinica');
    }
};
