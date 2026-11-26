<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Nivel 2: usuarios del sistema (no se registran por la web).
 * Contraseña de todas las cuentas de prueba: "password" (guardada con bcrypt).
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->administrador()->create([
            'nombre' => 'Administrador Ludan',
            'email' => 'admin@ludan.com',
        ]);

        User::factory()->supervisor()->create([
            'nombre' => 'Sofía Paredes Rojas',
            'email' => 'supervisor@ludan.com',
        ]);

        User::factory()->supervisor()->create([
            'nombre' => 'Martín Salazar Flores',
            'email' => 'supervisor2@ludan.com',
        ]);
    }
}
