<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postulaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacante_id')->constrained('vacantes')->cascadeOnDelete();
            $table->foreignId('perfil_postulante_id')->constrained('perfiles_postulante')->cascadeOnDelete();
            $table->text('carta_presentacion')->nullable();
            $table->decimal('porcentaje_match', 5, 2)->default(0);
            $table->enum('estado', ['postulado', 'en_revision', 'entrevista', 'rechazado', 'contratado'])->default('postulado');
            $table->timestamps();

            // Un postulante no puede postular dos veces a la misma vacante
            $table->unique(['vacante_id', 'perfil_postulante_id']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postulaciones');
    }
};
