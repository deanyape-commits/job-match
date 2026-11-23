<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Habilidad;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReclutadorVacantesTest extends TestCase
{
    use RefreshDatabase;

    private function datosVacante(array $cambios = []): array
    {
        $laravel = Habilidad::where('nombre', 'Laravel')->value('id');
        $docker = Habilidad::where('nombre', 'Docker')->value('id');

        return array_merge([
            'titulo' => 'Desarrollador Laravel Jr.',
            'categoria_id' => Categoria::value('id'),
            'ubicacion_id' => Ubicacion::value('id'),
            'nivel_experiencia' => 'junior',
            'modalidad' => 'remoto',
            'tipo_contrato' => 'tiempo_completo',
            'salario_min' => 3000,
            'salario_max' => 4000,
            'puestos' => 2,
            'descripcion' => 'Desarrollo de APIs con Laravel.',
            'requisitos' => '1 año de experiencia.',
            'habilidades' => [$laravel => 1, $docker => 0],
            'accion' => 'publicar',
        ], $cambios);
    }

    public function test_dashboard_muestra_indicadores_del_procedimiento_almacenado(): void
    {
        $reclutador = User::factory()->reclutador()->create();
        Vacante::factory()->count(2)->create(['empresa_id' => $reclutador->empresa_id, 'publicada_en' => now()]);

        $this->actingAs($reclutador)
            ->get(route('reclutador.inicio'))
            ->assertOk()
            ->assertSee('sp_reporte_empresa')
            ->assertSee('Vacantes publicadas');
    }

    public function test_reclutador_publica_una_vacante_con_habilidades(): void
    {
        $reclutador = User::factory()->reclutador()->create();

        $this->actingAs($reclutador)
            ->get(route('reclutador.vacantes.create'))
            ->assertOk()
            ->assertSee('Habilidades para el matching');

        $this->actingAs($reclutador)
            ->post(route('reclutador.vacantes.store'), $this->datosVacante())
            ->assertRedirect(route('reclutador.vacantes.index'));

        $this->actingAs($reclutador)
            ->get(route('reclutador.vacantes.edit', Vacante::first()))
            ->assertOk()
            ->assertSee('Desarrollador Laravel Jr.');

        $vacante = Vacante::first();
        $this->assertSame('publicada', $vacante->estado);
        $this->assertNotNull($vacante->publicada_en);
        $this->assertSame($reclutador->empresa_id, $vacante->empresa_id);
        $this->assertSame($reclutador->id, $vacante->reclutador_id);
        $this->assertCount(2, $vacante->habilidades);
        $this->assertTrue((bool) $vacante->habilidades->firstWhere('nombre', 'Laravel')->pivot->obligatoria);
        $this->assertFalse((bool) $vacante->habilidades->firstWhere('nombre', 'Docker')->pivot->obligatoria);
    }

    public function test_guardar_como_borrador(): void
    {
        $this->actingAs(User::factory()->reclutador()->create())
            ->post(route('reclutador.vacantes.store'), $this->datosVacante(['accion' => 'borrador']));

        $this->assertSame('borrador', Vacante::first()->estado);
        $this->assertNull(Vacante::first()->publicada_en);
    }

    public function test_la_vacante_necesita_al_menos_una_habilidad(): void
    {
        $this->actingAs(User::factory()->reclutador()->create())
            ->post(route('reclutador.vacantes.store'), $this->datosVacante(['habilidades' => []]))
            ->assertSessionHasErrors('habilidades');

        $this->assertSame(0, Vacante::count());
    }

    public function test_empresa_pendiente_no_puede_crear_vacantes(): void
    {
        $reclutador = User::factory()->reclutador(Empresa::factory()->pendiente()->create())->create();

        $this->actingAs($reclutador)->get(route('reclutador.vacantes.create'))->assertForbidden();
        $this->actingAs($reclutador)->post(route('reclutador.vacantes.store'), $this->datosVacante())->assertForbidden();
        $this->assertSame(0, Vacante::count());
    }

    public function test_reclutador_no_puede_editar_vacantes_de_otra_empresa(): void
    {
        $vacanteAjena = Vacante::factory()->create();
        $reclutador = User::factory()->reclutador()->create();

        $this->actingAs($reclutador)->get(route('reclutador.vacantes.edit', $vacanteAjena))->assertForbidden();
        $this->actingAs($reclutador)->put(route('reclutador.vacantes.update', $vacanteAjena), $this->datosVacante())->assertForbidden();
        $this->actingAs($reclutador)->delete(route('reclutador.vacantes.destroy', $vacanteAjena))->assertForbidden();
    }

    public function test_editar_cerrar_y_eliminar_una_vacante_propia(): void
    {
        $reclutador = User::factory()->reclutador()->create();
        $vacante = Vacante::factory()->create(['empresa_id' => $reclutador->empresa_id, 'reclutador_id' => $reclutador->id]);

        $this->actingAs($reclutador)
            ->put(route('reclutador.vacantes.update', $vacante), $this->datosVacante(['titulo' => 'Título nuevo']))
            ->assertRedirect(route('reclutador.vacantes.index'));
        $this->assertSame('Título nuevo', $vacante->fresh()->titulo);

        $this->actingAs($reclutador)->patch(route('reclutador.vacantes.cerrar', $vacante));
        $this->assertSame('cerrada', $vacante->fresh()->estado);

        $this->actingAs($reclutador)->delete(route('reclutador.vacantes.destroy', $vacante));
        $this->assertSoftDeleted($vacante);
    }

    public function test_listado_solo_muestra_vacantes_de_su_empresa(): void
    {
        $reclutador = User::factory()->reclutador()->create();
        Vacante::factory()->create(['empresa_id' => $reclutador->empresa_id, 'titulo' => 'Vacante Propia']);
        Vacante::factory()->create(['titulo' => 'Vacante Ajena']);

        $this->actingAs($reclutador)
            ->get(route('reclutador.vacantes.index'))
            ->assertOk()
            ->assertSee('Vacante Propia')
            ->assertDontSee('Vacante Ajena');
    }
}
