<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla pivote N:M: vacantes que un postulante marcó como favoritas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacantes_guardadas', function (Blueprint $table) {
            $table->foreignId('perfil_postulante_id')->constrained('perfiles_postulante')->cascadeOnDelete();
            $table->foreignId('vacante_id')->constrained('vacantes')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['perfil_postulante_id', 'vacante_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacantes_guardadas');
    }
};
