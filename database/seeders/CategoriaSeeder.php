<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Catálogo de áreas laborales del sector tecnológico.
 */
class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Desarrollo de software',
            'Desarrollo web',
            'Desarrollo móvil',
            'Datos y analítica',
            'Inteligencia artificial',
            'Ciberseguridad',
            'Redes y telecomunicaciones',
            'Cloud y DevOps',
            'Soporte TI',
            'QA y testing',
            'Diseño UX/UI',
            'Gestión de proyectos TI',
        ];

        foreach ($categorias as $nombre) {
            Categoria::firstOrCreate(['slug' => Str::slug($nombre)], ['nombre' => $nombre]);
        }
    }
}
