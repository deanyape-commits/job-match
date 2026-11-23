<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega a la tabla usuarios (creada por Breeze) las columnas propias de JobMatch.
 * Va después de roles y empresas porque tiene claves foráneas hacia ellas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('rol_id')->after('id')->constrained('roles');
            // Solo los reclutadores pertenecen a una empresa
            $table->foreignId('empresa_id')->nullable()->after('rol_id')->constrained('empresas')->nullOnDelete();
            $table->string('telefono', 20)->nullable()->after('email');
            $table->string('avatar_ruta')->nullable()->after('telefono');
            $table->boolean('activo')->default(true)->after('avatar_ruta');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('empresa_id');
            $table->dropConstrainedForeignId('rol_id');
            $table->dropColumn(['telefono', 'avatar_ruta', 'activo']);
        });
    }
};
