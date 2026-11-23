<?php

namespace Tests\Feature;

use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Endpoints JSON que usa el JavaScript para funcionar sin recargar la página.
 */
class FuncionesEnVivoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sugerencias_del_buscador(): void
    {
        Vacante::factory()->create(['titulo' => 'Desarrollador Laravel Senior']);
        Vacante::factory()->borrador()->create(['titulo' => 'Laravel Borrador Oculto']);

        $respuesta = $this->getJson(route('sugerencias', ['q' => 'lara']))->assertOk();

        $grupos = collect($respuesta->json('grupos'))->keyBy('titulo');
        $this->assertSame('Desarrollador Laravel Senior', $grupos['Vacantes']['items'][0]['texto']);
        $this->assertCount(1, $grupos['Vacantes']['items']); // el borrador no aparece
        $this->assertSame('Laravel', $grupos['Habilidades']['items'][0]['texto']);
    }

    public function test_sugerencias_con_menos_de_dos_letras_no_buscan(): void
    {
        $this->getJson(route('sugerencias', ['q' => 'l']))->assertOk()->assertExactJson(['grupos' => []]);
    }

    public function test_guardar_vacante_responde_json(): void
    {
        $postulante = User::factory()->postulante()->create();
        $vacante = Vacante::factory()->create();

        $this->actingAs($postulante)->postJson(route('postulante.vacantes.guardar', $vacante))
            ->assertOk()->assertJson(['guardada' => true]);

        $this->actingAs($postulante)->postJson(route('postulante.vacantes.guardar', $vacante))
            ->assertOk()->assertJson(['guardada' => false]);
    }

    public function test_cambiar_estado_responde_json_e_informa_cierre_de_vacante(): void
    {
        $reclutador = User::factory()->reclutador()->create();
        $vacante = Vacante::factory()->create(['empresa_id' => $reclutador->empresa_id, 'puestos' => 1]);
        $postulacion = Postulacion::create([
            'vacante_id' => $vacante->id,
            'perfil_postulante_id' => User::factory()->postulante()->create()->perfilPostulante->id,
        ]);

        $this->actingAs($reclutador)
            ->patchJson(route('reclutador.postulaciones.estado', $postulacion), ['estado' => 'contratado'])
            ->assertOk()
            ->assertJson(['estado' => 'contratado', 'vacante_cerrada' => true]);

        // Un segundo contratado ya no cabe: responde 422 con el mensaje
        $otra = Postulacion::create([
            'vacante_id' => $vacante->id,
            'perfil_postulante_id' => User::factory()->postulante()->create()->perfilPostulante->id,
        ]);
        $this->actingAs($reclutador)
            ->patchJson(route('reclutador.postulaciones.estado', $otra), ['estado' => 'contratado'])
            ->assertStatus(422)
            ->assertJsonPath('mensaje', 'Ya se cubrieron todos los puestos de esta vacante.');
    }

    public function test_resumen_de_notificaciones_requiere_sesion(): void
    {
        $this->getJson(route('notificaciones.resumen'))->assertUnauthorized();
    }
}
