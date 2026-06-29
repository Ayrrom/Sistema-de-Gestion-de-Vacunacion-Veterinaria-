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
            $table->string('motivo_consulta');
            $table->text('anamnesis')->nullable();

            // Parámetros fisiológicos
            $table->integer('fc')->nullable();
            $table->integer('fr')->nullable();
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->string('pulso')->nullable();
            $table->string('tllc')->nullable();
            $table->string('relacion_entorno')->nullable();

            // Exámenes
            $table->text('examen_fisico')->nullable();
            $table->text('examen_fisiologico')->nullable();

            // Estado general
            $table->string('hidratacion')->nullable();
            $table->boolean('esquema_completo')->default(false);
            $table->text('vacunas_aplicadas')->nullable();
            $table->text('desparasitacion')->nullable();

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
