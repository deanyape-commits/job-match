<?php

namespace Tests;

use Database\Seeders\CatalogoSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * En los tests que usan RefreshDatabase, carga los roles y catálogos
     * (ubicaciones, categorías, habilidades) después de migrar.
     */
    protected bool $seed = true;

    protected string $seeder = CatalogoSeeder::class;
}
