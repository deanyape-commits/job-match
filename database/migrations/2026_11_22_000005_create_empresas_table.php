<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ubicacion_id')->constrained('ubicaciones');
            $table->string('razon_social', 150);
            $table->char('ruc', 11)->unique();
            $table->text('descripcion')->nullable();
            $table->string('sitio_web')->nullable();
            $table->string('logo_ruta')->nullable();
            $table->enum('tamano', ['1-10', '11-50', '51-200', '201-500', '500+'])->default('1-10');
            $table->enum('estado', ['pendiente', 'verificada', 'rechazada'])->default('pendiente');
            // Supervisor que validó la empresa
            $table->foreignId('verificado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('verificado_en')->nullable();
            $table->timestamps();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
