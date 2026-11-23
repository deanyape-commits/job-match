<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoría de cambios de estado de las postulaciones.
 * La llena automáticamente el trigger trg_historial_postulacion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_postulaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulacion_id')->constrained('postulaciones')->cascadeOnDelete();
            $table->string('estado_anterior', 20)->nullable();
            $table->string('estado_nuevo', 20);
            $table->timestamp('cambiado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_postulaciones');
    }
};
