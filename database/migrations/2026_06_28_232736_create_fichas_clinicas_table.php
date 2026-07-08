<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fichas_clinicas', function (Blueprint $table) {
            $table->id();

            // Datos generales
            $table->date('fecha_consulta');
            $table->string('veterinario')->nullable();
            $table->string('motivo_consulta');
            $table->string('estado')->default('Sin consultas');

            // Vacunas
            $table->boolean('esquema_completo')->default(false);
            $table->text('vacunas_aplicadas')->nullable();

            // Desparasitación
            $table->date('fecha_desparasitacion')->nullable();
            $table->string('droga_desparasitacion')->nullable();
            $table->string('producto_desparasitacion')->nullable();

            // Parámetros fisiológicos
            $table->integer('fc')->nullable();
            $table->integer('fr')->nullable();
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->string('pulso')->nullable();
            $table->string('tllc')->nullable();
            $table->string('relacion_entorno')->nullable();

            // Estado de hidratación
            $table->boolean('hidratacion_normal')->default(true);
            $table->string('grado_deshidratacion')->nullable();

            // Examen físico (N = Normal, A = Anormal)
            $table->string('piel_anexos')->nullable();
            $table->string('ojos')->nullable();
            $table->string('oidos')->nullable();
            $table->string('ganglios_linfaticos')->nullable();
            $table->string('conjuntivas_mucosas')->nullable();
            $table->string('sistema_cardiaco')->nullable();
            $table->string('sistema_respiratorio')->nullable();
            $table->string('sistema_digestivo')->nullable();
            $table->string('sistema_urinario')->nullable();
            $table->string('sistema_nervioso')->nullable();
            $table->string('sistema_locomotor')->nullable();

            // Condición corporal (1 a 5)
            $table->unsignedTinyInteger('condicion_corporal')->nullable();

            // Relación con mascota
            $table->unsignedBigInteger('mascota_id');
            $table->foreign('mascota_id')->references('id')->on('mascotas');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fichas_clinicas');
    }
};
