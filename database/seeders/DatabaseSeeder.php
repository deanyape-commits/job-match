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

        User::factory()->postulante()->create([
            'nombre' => 'Luis Quispe Mamani',
            'email' => 'postulante@jobmatch.test',
        ]);
    }
}
