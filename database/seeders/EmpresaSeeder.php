<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Nivel 3: empresas y sus reclutadores (1 a 3 por empresa).
 * Depende de: ubicaciones (catálogo) y supervisores (quién verificó).
 */
class EmpresaSeeder extends Seeder
{
    public const VERIFICADAS = 50;

    public const PENDIENTES = 6;

    public const RECHAZADAS = 4;

    public function run(): void
    {
        $supervisores = User::whereHas('rol', fn ($q) => $q->where('slug', 'supervisor'))->pluck('id');

        // Empresa y reclutador de demostración
        $demo = Empresa::factory()->create([
            'razon_social' => 'Inka Fintech S.A.C.',
            'ruc' => '20512345678',
            'sitio_web' => 'https://inkafintech.pe',
            'tamano' => '51-200',
            'descripcion' => 'Fintech peruana que desarrolla billeteras digitales y pagos en línea para pequeños negocios.',
            'verificado_por' => $supervisores->first(),
        ]);
        User::factory()->reclutador($demo)->create([
            'nombre' => 'Carla Ramos Díaz',
            'email' => 'reclutador@jobmatch.com',
        ]);

        $crear = function (int $cantidad, string $estado) use ($supervisores) {
            Empresa::factory()->count($cantidad)->create([
                'estado' => $estado,
                'verificado_por' => $estado === 'pendiente' ? null : $supervisores->random(),
                'verificado_en' => $estado === 'pendiente' ? null : now()->subDays(rand(1, 200)),
            ])->each(function (Empresa $empresa) {
                User::factory()->reclutador($empresa)->count(rand(1, 3))->create();
            });
        };

        $crear(self::VERIFICADAS - 1, 'verificada');
        $crear(self::PENDIENTES, 'pendiente');
        $crear(self::RECHAZADAS, 'rechazada');
    }
}
