<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('reclutador_id')->constrained('usuarios');
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('ubicacion_id')->constrained('ubicaciones');
            $table->string('titulo', 150);
            $table->string('slug', 180)->unique();
            $table->text('descripcion');
            $table->text('requisitos');
            $table->enum('modalidad', ['presencial', 'remoto', 'hibrido']);
            $table->enum('tipo_contrato', ['tiempo_completo', 'medio_tiempo', 'practicas', 'freelance']);
            $table->enum('nivel_experiencia', ['practicante', 'junior', 'semi_senior', 'senior']);
            $table->decimal('salario_min', 10, 2)->nullable();
            $table->decimal('salario_max', 10, 2)->nullable();
            $table->unsignedSmallInteger('puestos')->default(1);
            $table->enum('estado', ['borrador', 'publicada', 'cerrada'])->default('borrador');
            $table->timestamp('publicada_en')->nullable();
            $table->timestamp('cierra_en')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'publicada_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacantes');
    }
};
