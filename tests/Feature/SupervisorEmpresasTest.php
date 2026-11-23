<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorEmpresasTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_ve_las_empresas_pendientes(): void
    {
        $pendiente = Empresa::factory()->pendiente()->create(['razon_social' => 'TecnoSierra E.I.R.L.']);
        Empresa::factory()->create(['razon_social' => 'Ya Verificada S.A.C.']);

        $this->actingAs(User::factory()->supervisor()->create())
            ->get(route('supervisor.empresas.index'))
            ->assertOk()
            ->assertSee($pendiente->razon_social)
            ->assertDontSee('Ya Verificada S.A.C.');
    }

    public function test_supervisor_verifica_una_empresa(): void
    {
        $supervisor = User::factory()->supervisor()->create();
        $empresa = Empresa::factory()->pendiente()->create();

        $this->actingAs($supervisor)
            ->patch(route('supervisor.empresas.verificar', $empresa))
            ->assertRedirect()
            ->assertSessionHas('exito');

        $empresa->refresh();
        $this->assertSame('verificada', $empresa->estado);
        $this->assertSame($supervisor->id, $empresa->verificado_por);
        $this->assertNotNull($empresa->verificado_en);
    }

    public function test_rechazar_una_empresa_cierra_sus_vacantes_publicadas(): void
    {
        $empresa = Empresa::factory()->create();
        $vacante = Vacante::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs(User::factory()->supervisor()->create())
            ->patch(route('supervisor.empresas.rechazar', $empresa));

        $this->assertSame('rechazada', $empresa->fresh()->estado);
        $this->assertSame('cerrada', $vacante->fresh()->estado);
    }

    public function test_reclutador_no_puede_verificar_empresas(): void
    {
        $empresa = Empresa::factory()->pendiente()->create();

        $this->actingAs(User::factory()->reclutador($empresa)->create())
            ->patch(route('supervisor.empresas.verificar', $empresa))
            ->assertForbidden();

        $this->assertSame('pendiente', $empresa->fresh()->estado);
    }
}
