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
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');           // obligatorio *
            $table->string('especie');          // obligatorio *
            $table->string('raza');             // obligatorio *
            $table->string('sexo');             // obligatorio *
            $table->decimal('peso', 5, 2);      // obligatorio *
            $table->integer('edad');            // obligatorio *
            $table->string('tipo_pelaje');      // obligatorio *
            $table->date('fecha_nacimiento')->nullable();      // opcional
            $table->boolean('castrado')->default(false);       // opcional
            $table->string('color')->nullable();               // opcional
            $table->string('procedencia')->nullable();         // opcional
            $table->text('senas_particulares')->nullable();    // opcional
            $table->string('propietario_dni');
            $table->foreign('propietario_dni')->references('dni')->on('propietarios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};
