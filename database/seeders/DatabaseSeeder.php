<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Habilidad;
use App\Models\Rol;
use App\Models\User;
use App\Models\Vacante;
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
        $this->call(CatalogoSeeder::class);

        User::factory()->create([
            'rol_id' => Rol::where('slug', Rol::ADMINISTRADOR)->value('id'),
            'nombre' => 'Administrador JobMatch',
            'email' => 'admin@jobmatch.test',
        ]);

        User::factory()->create([
            'rol_id' => Rol::where('slug', Rol::SUPERVISOR)->value('id'),
            'nombre' => 'Supervisor JobMatch',
            'email' => 'supervisor@jobmatch.test',
        ]);

        // --- Datos de demostración (provisionales hasta la fase de datos de prueba) ---
        $empresa = Empresa::factory()->create(['razon_social' => 'Inka Fintech S.A.C.', 'ruc' => '20512345678']);
        $reclutador = User::factory()->reclutador($empresa)->create([
            'nombre' => 'Carla Ramos Díaz',
            'email' => 'reclutador@jobmatch.test',
        ]);
        Vacante::factory()->count(4)->create(['empresa_id' => $empresa->id, 'reclutador_id' => $reclutador->id])
            ->each(fn (Vacante $v) => $v->habilidades()->attach(
                Habilidad::inRandomOrder()->limit(5)->pluck('id')->mapWithKeys(fn ($id, $i) => [$id => ['obligatoria' => $i < 3]])
            ));

        // Empresas esperando verificación del supervisor
        Empresa::factory()->pendiente()->count(3)->create()
            ->each(fn (Empresa $e) => User::factory()->reclutador($e)->create());

        // Más empresas verificadas con vacantes, para que el buscador tenga contenido
        Empresa::factory()->count(6)->create()->each(function (Empresa $e) {
            $reclutador = User::factory()->reclutador($e)->create();
            Vacante::factory()->count(3)->create(['empresa_id' => $e->id, 'reclutador_id' => $reclutador->id])
                ->each(fn (Vacante $v) => $v->habilidades()->attach(
                    Habilidad::where('tipo', 'tecnica')->inRandomOrder()->limit(5)->pluck('id')
                        ->mapWithKeys(fn ($id, $i) => [$id => ['obligatoria' => $i < 3]])
                ));
        });

        // Postulantes que ya postularon a las vacantes de Inka Fintech (para ver candidatos)
        $matching = app(\App\Services\MatchingService::class);
        $tecnicas = Habilidad::where('tipo', 'tecnica')->pluck('id');
        $vacantesInka = Vacante::where('empresa_id', $empresa->id)->with('habilidades')->get();
        User::factory()->postulante()->count(12)->create()->each(function (User $u) use ($matching, $tecnicas, $vacantesInka) {
            $perfil = $u->perfilPostulante;
            // Mezcla habilidades de las vacantes con otras al azar, para que el match varíe
            $ids = $vacantesInka->random()->habilidades->pluck('id')->random(rand(1, 4))
                ->merge($tecnicas->random(rand(2, 5)))->unique();
            $perfil->habilidades()->sync($ids->mapWithKeys(fn ($id) => [$id => ['nivel' => rand(1, 5)]]));

            foreach ($vacantesInka->random(rand(1, 2)) as $vacante) {
                $postulacion = $perfil->postulaciones()->create([
                    'vacante_id' => $vacante->id,
                    'porcentaje_match' => $matching->calcular($perfil, $vacante)['porcentaje'],
                    'created_at' => now()->subDays(rand(0, 40)),
                ]);
                $estado = collect(['postulado', 'postulado', 'en_revision', 'en_revision', 'entrevista', 'rechazado'])->random();
                if ($estado !== 'postulado') {
                    $postulacion->update(['estado' => $estado]); // el trigger registra el historial
                }
                if ($estado === 'entrevista') {
                    $postulacion->entrevistas()->create([
                        'fecha_hora' => now()->addDays(rand(1, 6))->setTime(rand(9, 17), 0),
                        'enlace_reunion' => \App\Models\Entrevista::generarEnlaceJitsi(),
                    ]);
                }
            }
        });

        // Postulante de demostración con perfil completo
        $postulante = User::factory()->postulante()->create([
            'nombre' => 'Luis Quispe Mamani',
            'email' => 'postulante@jobmatch.test',
        ]);
        $perfil = $postulante->perfilPostulante;
        $perfil->update([
            'titular' => 'Desarrollador Backend Jr.',
            'ubicacion_id' => \App\Models\Ubicacion::where('departamento', 'Lima')->value('id'),
            'anios_experiencia' => 2,
        ]);
        $perfil->habilidades()->sync(
            Habilidad::whereIn('nombre', ['PHP', 'Laravel', 'PostgreSQL', 'SQL', 'Git', 'JavaScript', 'Vue.js', 'Docker', 'Trabajo en equipo', 'Inglés intermedio'])
                ->pluck('id')->mapWithKeys(fn ($id) => [$id => ['nivel' => rand(2, 5)]])
        );
        $perfil->experiencias()->create([
            'empresa_nombre' => 'Andina Software S.A.C.', 'cargo' => 'Practicante de desarrollo',
            'fecha_inicio' => now()->subYears(2), 'fecha_fin' => now()->subYear(),
            'descripcion' => 'Desarrollo de módulos en Laravel y mantenimiento de la base de datos.',
        ]);
        $perfil->estudios()->create([
            'institucion' => 'Tecsup', 'grado' => 'tecnico', 'carrera' => 'Diseño y Desarrollo de Software',
            'fecha_inicio' => now()->subYears(4), 'fecha_fin' => now()->subYear(),
        ]);
    }
}
