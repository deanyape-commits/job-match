<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('departamento', 60);
            $table->string('provincia', 60);
            $table->timestamps();

            $table->unique(['departamento', 'provincia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones');
    }
};
