<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla pivote N:M entre vacantes y habilidades.
 * "obligatoria" define el peso en el cálculo del matching.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacante_habilidad', function (Blueprint $table) {
            $table->foreignId('vacante_id')->constrained('vacantes')->cascadeOnDelete();
            $table->foreignId('habilidad_id')->constrained('habilidades')->cascadeOnDelete();
            $table->boolean('obligatoria')->default(true);

            $table->primary(['vacante_id', 'habilidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacante_habilidad');
    }
};
