<?php

namespace Tests;

use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * En los tests que usan RefreshDatabase, carga los roles
     * después de migrar (los usuarios necesitan un rol_id).
     */
    protected bool $seed = true;

    protected string $seeder = RolSeeder::class;
}
