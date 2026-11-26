<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeders JERÁRQUICOS: cada nivel depende solo de los anteriores,
 * así se respeta la integridad referencial (claves foráneas).
 *
 *   1. CatalogoSeeder     → roles, ubicaciones, categorías, habilidades
 *   2. UsuarioSeeder      → administrador y supervisores
 *   3. EmpresaSeeder      → 60 empresas con sus reclutadores
 *   4. PostulanteSeeder   → 1 000 postulantes con perfil, experiencia, estudios y habilidades
 *   5. VacanteSeeder      → 500 vacantes con sus habilidades
 *   6. PostulacionSeeder  → ~3 700 postulaciones (match real) y entrevistas
 *
 * Cuentas de prueba (contraseña "password"):
 *   admin@ludan.com · supervisor@ludan.com · reclutador@ludan.com · postulante@ludan.com
 *
 * Uso: php artisan migrate:fresh --seed   (tarda alrededor de 1 minuto)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogoSeeder::class,
            UsuarioSeeder::class,
            EmpresaSeeder::class,
            PostulanteSeeder::class,
            VacanteSeeder::class,
            PostulacionSeeder::class,
        ]);
    }
}
