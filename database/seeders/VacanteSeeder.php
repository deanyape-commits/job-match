<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Habilidad;
use App\Models\Ubicacion;
use App\Models\Vacante;
use Database\Seeders\Datos\AreasTi;
use Database\Seeders\Datos\Textos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Nivel 5: vacantes de las empresas verificadas, con sus habilidades (N:M).
 * Depende de: empresas, reclutadores, categorías, ubicaciones y habilidades.
 */
class VacanteSeeder extends Seeder
{
    public const CANTIDAD = 500;

    public function run(): void
    {
        $habilidades = Habilidad::pluck('id', 'nombre');
        $categorias = Categoria::pluck('id', 'slug');
        $lima = Ubicacion::where('departamento', 'Lima')->pluck('id');
        $todas = Ubicacion::pluck('id');
        $empresas = Empresa::where('estado', 'verificada')->with('reclutadores')->get();
        $demo = $empresas->firstWhere('ruc', '20512345678');

        DB::transaction(function () use ($habilidades, $categorias, $lima, $todas, $empresas, $demo) {
            for ($i = 0; $i < self::CANTIDAD; $i++) {
                // Las primeras 8 son de la empresa de demostración (áreas de desarrollo)
                $esDemo = $i < 8;
                $empresa = $esDemo ? $demo : $empresas->random();
                $area = $esDemo ? Arr::random(['desarrollo-web', 'desarrollo-de-software', 'qa-y-testing', 'datos-y-analitica']) : array_rand(AreasTi::AREAS);
                $datos = AreasTi::AREAS[$area];

                // 80 % publicadas, 12 % cerradas, 8 % borradores
                $estado = Arr::random(array_merge(array_fill(0, 80, 'publicada'), array_fill(0, 12, 'cerrada'), array_fill(0, 8, 'borrador')));
                $publicadaEn = $estado === 'borrador' ? null : now()->subDays(rand(0, 170))->subHours(rand(0, 23));

                $titulo = Arr::random($datos['puestos']);
                $vacante = Vacante::factory()->create([
                    'empresa_id' => $empresa->id,
                    'reclutador_id' => $empresa->reclutadores->random()->id,
                    'categoria_id' => $categorias[$area],
                    'ubicacion_id' => fake()->boolean(55) ? $lima->random() : $todas->random(),
                    'titulo' => $titulo,
                    'slug' => Str::slug($titulo).'-'.Str::lower(Str::random(6)),
                    'descripcion' => $this->descripcion($titulo, $empresa->razon_social),
                    'estado' => $estado,
                    'publicada_en' => $publicadaEn,
                    'cierra_en' => match ($estado) {
                        'cerrada' => $publicadaEn?->copy()->addDays(rand(20, 45)),
                        default => now()->addDays(rand(7, 60)),
                    },
                ]);

                // 3 a 6 habilidades del área (la mayoría obligatorias) + 0 a 2 complementarias deseables
                $tecnicas = Arr::random($datos['habilidades'], min(count($datos['habilidades']), rand(3, 6)));
                $pivote = collect($tecnicas)->mapWithKeys(fn ($n, $k) => [$habilidades[$n] => ['obligatoria' => $k < 3 || fake()->boolean(40)]]);
                foreach (Arr::random(AreasTi::COMPLEMENTARIAS, rand(0, 2)) as $nombre) {
                    $pivote[$habilidades[$nombre]] = ['obligatoria' => false];
                }
                $vacante->habilidades()->attach($pivote);
            }
        });
    }

    private function descripcion(string $titulo, string $empresa): string
    {
        return "En {$empresa} buscamos un(a) {$titulo} para sumarse a nuestro equipo de tecnología.\n\n"
            .'Participarás en proyectos para clientes de distintos sectores, trabajando con metodologías ágiles junto a un equipo multidisciplinario. '
            ."Ofrecemos línea de carrera, capacitaciones y un buen ambiente de trabajo.\n\n"
            ."Funciones principales:\n".Textos::lista(Textos::FUNCIONES, 3, 5);
    }
}
