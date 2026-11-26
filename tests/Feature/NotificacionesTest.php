<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use App\Notifications\EmpresaRevisada;
use App\Notifications\EntrevistaAgendada;
use App\Notifications\EstadoPostulacionCambiado;
use App\Notifications\NuevaPostulacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificacionesTest extends TestCase
{
    use RefreshDatabase;

    private function escenario(): array
    {
        $reclutador = User::factory()->reclutador()->create();
        $vacante = Vacante::factory()->create(['empresa_id' => $reclutador->empresa_id, 'reclutador_id' => $reclutador->id]);
        $postulante = User::factory()->postulante()->create();
        $postulacion = Postulacion::create([
            'vacante_id' => $vacante->id,
            'perfil_postulante_id' => $postulante->perfilPostulante->id,
            'porcentaje_match' => 80,
        ]);

        return [$reclutador, $vacante, $postulante, $postulacion];
    }

    public function test_postular_avisa_al_reclutador(): void
    {
        Notification::fake();
        [$reclutador, $vacante] = $this->escenario();
        $otro = User::factory()->postulante()->create();

        $this->actingAs($otro)->post(route('postulante.vacantes.postular', $vacante));

        Notification::assertSentTo($reclutador, NuevaPostulacion::class);
    }

    public function test_cambiar_estado_avisa_al_postulante_por_correo_y_campanita(): void
    {
        Notification::fake();
        [$reclutador, , $postulante, $postulacion] = $this->escenario();

        $this->actingAs($reclutador)->patch(route('reclutador.postulaciones.estado', $postulacion), ['estado' => 'en_revision']);

        Notification::assertSentTo($postulante, EstadoPostulacionCambiado::class,
            fn ($n, $canales) => $canales === ['mail', 'database']);
    }

    public function test_no_avisa_si_el_estado_no_cambia(): void
    {
        Notification::fake();
        [$reclutador, , $postulante, $postulacion] = $this->escenario();

        $this->actingAs($reclutador)->patch(route('reclutador.postulaciones.estado', $postulacion), ['estado' => 'postulado']);

        Notification::assertNotSentTo($postulante, EstadoPostulacionCambiado::class);
    }

    public function test_agendar_entrevista_envia_el_enlace_de_jitsi(): void
    {
        Notification::fake();
        [$reclutador, , $postulante, $postulacion] = $this->escenario();

        $this->actingAs($reclutador)->post(route('reclutador.entrevistas.store', $postulacion), [
            'fecha_hora' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'duracion_minutos' => 30,
        ]);

        Notification::assertSentTo($postulante, EntrevistaAgendada::class, function ($n) use ($postulante) {
            $correo = $n->toMail($postulante);

            return str_starts_with($correo->actionUrl, 'https://meet.jit.si/ludan-');
        });
    }

    public function test_verificar_empresa_avisa_a_sus_reclutadores(): void
    {
        Notification::fake();
        $empresa = Empresa::factory()->pendiente()->create();
        $reclutadores = User::factory()->reclutador($empresa)->count(2)->create();

        $this->actingAs(User::factory()->supervisor()->create())->patch(route('supervisor.empresas.verificar', $empresa));

        Notification::assertSentTo($reclutadores, EmpresaRevisada::class);
    }

    public function test_los_correos_se_generan_en_espanol(): void
    {
        [, , $postulante, $postulacion] = $this->escenario();
        $postulacion->update(['estado' => 'contratado']);

        $html = (string) (new EstadoPostulacionCambiado($postulacion))->toMail($postulante)->render();

        $this->assertStringContainsString('Felicitaciones', $html);
        $this->assertStringContainsString('Ver mi postulación', $html);
    }

    public function test_campanita_muestra_la_notificacion_y_al_abrirla_se_marca_como_leida(): void
    {
        [, , $postulante, $postulacion] = $this->escenario();
        $postulacion->update(['estado' => 'en_revision']);
        $postulante->notifyNow(new EstadoPostulacionCambiado($postulacion), ['database']);

        $this->actingAs($postulante)->get(route('postulante.vacantes.index'))
            ->assertSee('1 sin leer');

        // La campanita consulta este resumen cada 30 segundos
        $this->actingAs($postulante)->getJson(route('notificaciones.resumen'))
            ->assertOk()
            ->assertJsonPath('sin_leer', 1)
            ->assertJsonPath('items.0.mensaje', 'La empresa está revisando tu perfil.');

        $notificacion = $postulante->notifications()->first();
        $this->actingAs($postulante)->get(route('notificaciones.abrir', $notificacion->id))
            ->assertRedirect(route('postulante.postulaciones.show', $postulacion));

        $this->assertNotNull($notificacion->fresh()->read_at);
        $this->actingAs($postulante)->get(route('notificaciones.index'))->assertOk();
    }

    public function test_si_el_correo_falla_la_accion_continua_y_queda_la_campanita(): void
    {
        [$reclutador, , $postulante, $postulacion] = $this->escenario();
        Mail::shouldReceive('mailer')->andThrow(new \RuntimeException('Servidor SMTP apagado'));

        $this->actingAs($reclutador)
            ->patch(route('reclutador.postulaciones.estado', $postulacion), ['estado' => 'en_revision'])
            ->assertSessionHas('exito');

        $this->assertSame('en_revision', $postulacion->fresh()->estado);
        $this->assertSame(1, $postulante->notifications()->count());
    }
}
