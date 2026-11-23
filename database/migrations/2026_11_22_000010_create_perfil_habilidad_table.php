<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla pivote N:M entre perfiles de postulante y habilidades.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfil_habilidad', function (Blueprint $table) {
            $table->foreignId('perfil_postulante_id')->constrained('perfiles_postulante')->cascadeOnDelete();
            $table->foreignId('habilidad_id')->constrained('habilidades')->cascadeOnDelete();
            $table->unsignedSmallInteger('nivel')->default(3); // 1 (básico) a 5 (experto)

            $table->primary(['perfil_postulante_id', 'habilidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_habilidad');
    }
};
