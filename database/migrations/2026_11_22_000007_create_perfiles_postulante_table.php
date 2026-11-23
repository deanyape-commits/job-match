<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfiles_postulante', function (Blueprint $table) {
            $table->id();
            // UNIQUE: cada usuario tiene como máximo un perfil (relación 1:1)
            $table->foreignId('usuario_id')->unique()->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->string('titular', 150)->nullable();
            $table->text('resumen')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->unsignedSmallInteger('anios_experiencia')->default(0);
            $table->decimal('salario_esperado', 10, 2)->nullable();
            $table->string('cv_ruta')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('portafolio_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfiles_postulante');
    }
};
