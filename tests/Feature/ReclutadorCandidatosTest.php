<?php

namespace Tests\Feature;

use App\Models\Entrevista;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReclutadorCandidatosTest extends TestCase
{
    use RefreshDatabase;

    private User $reclutador;

    private Vacante $vacante;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reclutador = User::factory()->reclutador()->create();
        $this->vacante = Vacante::factory()->create([
            'empresa_id' => $this->reclutador->empresa_id,
            'reclutador_id' => $this->reclutador->id,
            'puestos' => 1,
        ]);
    }

    private function postulacion(float $match = 50, string $nombre = 'Candidato'): Postulacion
    {
        $postulante = User::factory()->postulante()->create(['nombre' => $nombre]);

        return Postulacion::create([
            'vacante_id' => $this->vacante->id,
            'perfil_postulante_id' => $postulante->perfilPostulante->id,
            'porcentaje_match' => $match,
        ]);
    }

    public function test_candidatos_ordenados_por_match(): void
    {
        $this->postulacion(40, 'Bajo Match');
        $this->postulacion(95, 'Alto Match');

        $this->actingAs($this->reclutador)
            ->get(route('reclutador.vacantes.candidatos', $this->vacante))
            ->assertOk()
            ->assertSeeInOrder(['Alto Match', 'Bajo Match']);
    }

    public function test_filtrar_candidatos_por_estado(): void
    {
        $this->postulacion(50, 'En Revision Uno')->update(['estado' => 'en_revision']);
        $this->postulacion(50, 'Recien Postulado');

        $this->actingAs($this->reclutador)
            ->get(route('reclutador.vacantes.candidatos', ['vacante' => $this->vacante, 'estado' => 'en_revision']))
            ->assertSee('En Revision Uno')
            ->assertDontSee('Recien Postulado');
    }

    public function test_ver_perfil_del_candidato(): void
    {
        $postulacion = $this->postulacion(80, 'María Huamán');

        $this->actingAs($this->reclutador)
            ->get(route('reclutador.postulaciones.show', $postulacion))
            ->assertOk()
            ->assertSee('María Huamán')
            ->assertSee('Agendar entrevista');
    }

    public function test_cambiar_estado_registra_historial(): void
    {
        $postulacion = $this->postulacion();

        $this->actingAs($this->reclutador)
            ->patch(route('reclutador.postulaciones.estado', $postulacion), ['estado' => 'en_revision'])
            ->assertSessionHas('exito');

        $this->assertSame('en_revision', $postulacion->fresh()->estado);
        $this->assertSame(['postulado', 'en_revision'], $postulacion->historial()->pluck('estado_nuevo')->all());
    }

    public function test_contratar_cubre_los_puestos_y_el_trigger_cierra_la_vacante(): void
    {
        $postulacion = $this->postulacion();

        $this->actingAs($this->reclutador)
            ->patch(route('reclutador.postulaciones.estado', $postulacion), ['estado' => 'contratado'])
            ->assertSessionHas('exito', fn ($m) => str_contains($m, 'se cerró automáticamente'));

        $this->assertSame('cerrada', $this->vacante->fresh()->estado);
    }

    public function test_no_se_puede_contratar_mas_que_los_puestos(): void
    {
        $this->postulacion()->update(['estado' => 'contratado']);
        $otra = $this->postulacion();

        $this->actingAs($this->reclutador)
            ->patch(route('reclutador.postulaciones.estado', $otra), ['estado' => 'contratado'])
            ->assertSessionHas('error');

        $this->assertSame('postulado', $otra->fresh()->estado);
    }

    public function test_agendar_entrevista_genera_enlace_jitsi_y_cambia_estado(): void
    {
        $postulacion = $this->postulacion();

        $this->actingAs($this->reclutador)
            ->post(route('reclutador.entrevistas.store', $postulacion), [
                'fecha_hora' => now()->addDays(2)->format('Y-m-d\TH:i'),
                'duracion_minutos' => 30,
            ])
            ->assertRedirect(route('reclutador.postulaciones.show', $postulacion));

        $entrevista = Entrevista::first();
        $this->assertStringStartsWith('https://meet.jit.si/ludan-', $entrevista->enlace_reunion);
        $this->assertSame('programada', $entrevista->estado);
        $this->assertSame('entrevista', $postulacion->fresh()->estado);

        // Aparece en la lista de entrevistas del reclutador y en "Mis postulaciones" del postulante
        $this->actingAs($this->reclutador)->get(route('reclutador.entrevistas.index'))->assertSee($entrevista->enlace_reunion);
        $this->actingAs($postulacion->perfilPostulante->usuario)->get(route('postulante.postulaciones.index'))
            ->assertSee('Próxima entrevista')
            ->assertSee($entrevista->enlace_reunion);
    }

    public function test_entrevista_en_el_pasado_no_se_permite(): void
    {
        $this->actingAs($this->reclutador)
            ->post(route('reclutador.entrevistas.store', $this->postulacion()), [
                'fecha_hora' => now()->subDay()->format('Y-m-d\TH:i'),
                'duracion_minutos' => 30,
            ])
            ->assertSessionHasErrorsIn('entrevista', 'fecha_hora');

        $this->assertSame(0, Entrevista::count());
    }

    public function test_marcar_entrevista_como_realizada(): void
    {
        $entrevista = $this->postulacion()->entrevistas()->create([
            'fecha_hora' => now()->addDay(),
            'enlace_reunion' => Entrevista::generarEnlaceJitsi(),
        ]);

        $this->actingAs($this->reclutador)
            ->patch(route('reclutador.entrevistas.update', $entrevista), ['estado' => 'realizada']);

        $this->assertSame('realizada', $entrevista->fresh()->estado);
    }

    public function test_reclutador_de_otra_empresa_no_puede_gestionar_candidatos(): void
    {
        $postulacion = $this->postulacion();
        $otro = User::factory()->reclutador()->create();

        $this->actingAs($otro)->get(route('reclutador.vacantes.candidatos', $this->vacante))->assertForbidden();
        $this->actingAs($otro)->get(route('reclutador.postulaciones.show', $postulacion))->assertForbidden();
        $this->actingAs($otro)->patch(route('reclutador.postulaciones.estado', $postulacion), ['estado' => 'rechazado'])->assertForbidden();
        $this->actingAs($otro)->post(route('reclutador.entrevistas.store', $postulacion), [
            'fecha_hora' => now()->addDay()->format('Y-m-d\TH:i'), 'duracion_minutos' => 30,
        ])->assertForbidden();

        $this->assertSame('postulado', $postulacion->fresh()->estado);
    }
}
