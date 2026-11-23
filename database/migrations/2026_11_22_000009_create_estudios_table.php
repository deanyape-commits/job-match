<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_postulante_id')->constrained('perfiles_postulante')->cascadeOnDelete();
            $table->string('institucion', 150);
            $table->enum('grado', ['tecnico', 'bachiller', 'titulado', 'maestria', 'curso']);
            $table->string('carrera', 150);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable(); // null = en curso
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudios');
    }
};
