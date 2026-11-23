<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrevistas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postulacion_id')->constrained('postulaciones')->cascadeOnDelete();
            $table->timestamp('fecha_hora');
            $table->unsignedSmallInteger('duracion_minutos')->default(30);
            $table->string('enlace_reunion'); // https://meet.jit.si/jobmatch-xxxx
            $table->enum('estado', ['programada', 'realizada', 'cancelada'])->default('programada');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrevistas');
    }
};
