<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeders jerárquicos: primero lo que no depende de nada (roles, catálogos),
     * luego usuarios y al final las entidades dependientes.
     * Los demás seeders (catálogos, empresas, vacantes...) se agregan en la fase de datos de prueba.
     */
    public function run(): void
    {
        $this->call(RolSeeder::class);

        User::factory()->create([
            'rol_id' => Rol::where('slug', Rol::ADMINISTRADOR)->value('id'),
            'nombre' => 'Administrador JobMatch',
            'email' => 'admin@jobmatch.test',
        ]);
    }
}
