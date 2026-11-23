<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_postulante_id')->constrained('perfiles_postulante')->cascadeOnDelete();
            $table->string('empresa_nombre', 150);
            $table->string('cargo', 150);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable(); // null = trabajo actual
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiencias');
    }
};
