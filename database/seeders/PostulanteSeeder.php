<?php

namespace Database\Seeders;

use App\Models\Estudio;
use App\Models\Experiencia;
use App\Models\Habilidad;
use App\Models\PerfilPostulante;
use App\Models\Ubicacion;
use App\Models\User;
use Database\Seeders\Datos\AreasTi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Nivel 4: postulantes con su perfil (1:1), experiencias, estudios y habilidades.
 * Cada postulante pertenece a un "área" para que sus habilidades sean coherentes.
 */
class PostulanteSeeder extends Seeder
{
    public const CANTIDAD = 1000;

    public function run(): void
    {
        $habilidades = Habilidad::pluck('id', 'nombre');
        $lima = Ubicacion::where('departamento', 'Lima')->pluck('id');
        $todas = Ubicacion::pluck('id');

        DB::transaction(function () use ($habilidades, $lima, $todas) {
            $this->crearPostulanteDemo($habilidades, $lima->first());

            for ($i = 0; $i < self::CANTIDAD - 1; $i++) {
                $area = array_rand(AreasTi::AREAS);
                $datos = AreasTi::AREAS[$area];
                $anios = fake()->numberBetween(0, 10);

                $usuario = User::factory()->create(); // rol postulante por defecto
                $perfil = PerfilPostulante::factory()->create([
                    'usuario_id' => $usuario->id,
                    // ~45 % en Lima, como en el mercado real
                    'ubicacion_id' => fake()->boolean(45) ? $lima->random() : $todas->random(),
                    'titular' => Arr::random($datos['titulares']).($anios < 2 ? ' Jr.' : ($anios > 6 ? ' Sr.' : '')),
                    'anios_experiencia' => $anios,
                ]);

                // Habilidades: varias de su área, alguna de otra área y complementarias
                $otraArea = AreasTi::AREAS[array_rand(AreasTi::AREAS)]['habilidades'];
                $nombres = collect(Arr::random($datos['habilidades'], min(count($datos['habilidades']), rand(3, 8))))
                    ->merge(Arr::random($otraArea, rand(0, 2)))
                    ->merge(Arr::random(AreasTi::COMPLEMENTARIAS, rand(1, 3)))
                    ->unique();
                $perfil->habilidades()->attach(
                    $nombres->mapWithKeys(fn ($n) => [$habilidades[$n] => ['nivel' => rand(1, 5)]])
                );

                Experiencia::factory()->count(min(3, intdiv($anios, 2) + rand(0, 1)))->create(['perfil_postulante_id' => $perfil->id]);
                Estudio::factory()->count(rand(1, 2))->create(['perfil_postulante_id' => $perfil->id]);
            }
        });
    }

    /** Postulante de demostración con perfil completo. */
    private function crearPostulanteDemo($habilidades, int $lima): void
    {
        $usuario = User::factory()->create([
            'nombre' => 'Luis Quispe Mamani',
            'email' => 'postulante@ludan.com',
        ]);

        $perfil = PerfilPostulante::factory()->create([
            'usuario_id' => $usuario->id,
            'ubicacion_id' => $lima,
            'titular' => 'Desarrollador Backend Jr.',
            'anios_experiencia' => 2,
            'salario_esperado' => 3500,
            'resumen' => 'Egresado de Tecsup en Diseño y Desarrollo de Software. Me especializo en el desarrollo de APIs con Laravel y PostgreSQL, y me interesa crecer en arquitectura de software.',
        ]);

        $perfil->habilidades()->attach(collect([
            'PHP' => 4, 'Laravel' => 4, 'PostgreSQL' => 3, 'SQL' => 4, 'Git' => 4, 'APIs REST' => 3,
            'JavaScript' => 3, 'Vue.js' => 2, 'Docker' => 2, 'Trabajo en equipo' => 4, 'Inglés intermedio' => 3,
        ])->mapWithKeys(fn ($nivel, $nombre) => [$habilidades[$nombre] => ['nivel' => $nivel]]));

        $perfil->experiencias()->create([
            'empresa_nombre' => 'Andina Software S.A.C.', 'cargo' => 'Practicante de desarrollo',
            'fecha_inicio' => now()->subYears(2), 'fecha_fin' => now()->subYear(),
            'descripcion' => 'Desarrollo de módulos en Laravel y mantenimiento de la base de datos PostgreSQL.',
        ]);
        $perfil->estudios()->create([
            'institucion' => 'Tecsup', 'grado' => 'tecnico', 'carrera' => 'Diseño y Desarrollo de Software',
            'fecha_inicio' => now()->subYears(4), 'fecha_fin' => now()->subYear(),
        ]);
    }
}
