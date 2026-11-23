<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Datos base que no dependen de nada: roles y catálogos.
 * Lo usan DatabaseSeeder y los tests.
 */
class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            UbicacionSeeder::class,
            CategoriaSeeder::class,
            HabilidadSeeder::class,
        ]);
    }
}
