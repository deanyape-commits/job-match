<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InicioTest extends TestCase
{
    use RefreshDatabase;

    public function test_inicio_publico_muestra_vacantes_recientes_y_cifras(): void
    {
        Vacante::factory()->create(['titulo' => 'Vacante Reciente Visible']);
        Vacante::factory()->borrador()->create(['titulo' => 'Borrador Oculto']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Encuentra el trabajo que coincide con')
            ->assertSee('img/logo-negro.png')
            ->assertSee('Empresas verificadas que publican en Ludan')
            ->assertSee('Vacante Reciente Visible')
            ->assertDontSee('Borrador Oculto')
            ->assertSee('Crear cuenta');
    }

    public function test_usuario_con_sesion_ve_el_boton_a_su_panel(): void
    {
        $this->actingAs(User::factory()->postulante()->create())
            ->get('/')
            ->assertSee('Ir a mi panel')
            ->assertDontSee('Iniciar sesión');
    }

    public function test_invitado_que_busca_es_llevado_al_login_y_luego_a_la_busqueda(): void
    {
        $this->get(route('postulante.vacantes.index', ['q' => 'Laravel']))->assertRedirect(route('login'));

        $postulante = User::factory()->postulante()->create();
        $this->post('/login', ['email' => $postulante->email, 'password' => 'password'])
            ->assertRedirect(route('postulante.vacantes.index', ['q' => 'Laravel']));
    }
}
