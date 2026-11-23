<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Rol;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesYPermisosTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioConRol(string $slug, array $extra = []): User
    {
        return User::factory()->create(['rol_id' => Rol::where('slug', $slug)->value('id')] + $extra);
    }

    // ---------------- Registro ----------------

    public function test_registro_como_postulante_crea_su_perfil(): void
    {
        $this->post('/register', [
            'tipo_cuenta' => 'postulante',
            'nombre' => 'Luis Quispe',
            'email' => 'luis@correo.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $usuario = User::where('email', 'luis@correo.test')->first();

        $this->assertTrue($usuario->tieneRol(Rol::POSTULANTE));
        $this->assertNotNull($usuario->perfilPostulante);
        $this->assertNull($usuario->empresa_id);
    }

    public function test_registro_como_reclutador_crea_empresa_pendiente(): void
    {
        $ubicacion = Ubicacion::firstOrCreate(['departamento' => 'Lima', 'provincia' => 'Lima']);

        $this->post('/register', [
            'tipo_cuenta' => 'reclutador',
            'nombre' => 'Carla Ramos',
            'email' => 'carla@empresa.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'razon_social' => 'Inka Fintech S.A.C.',
            'ruc' => '20512345678',
            'ubicacion_id' => $ubicacion->id,
        ])->assertRedirect(route('dashboard', absolute: false));

        $usuario = User::where('email', 'carla@empresa.test')->first();

        $this->assertTrue($usuario->tieneRol(Rol::RECLUTADOR));
        $this->assertSame('Inka Fintech S.A.C.', $usuario->empresa->razon_social);
        $this->assertSame('pendiente', $usuario->empresa->estado);
        $this->assertNull($usuario->perfilPostulante);
    }

    public function test_registro_como_reclutador_exige_datos_de_empresa_validos(): void
    {
        $this->post('/register', [
            'tipo_cuenta' => 'reclutador',
            'nombre' => 'Carla Ramos',
            'email' => 'carla@empresa.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'ruc' => '12345',
        ])->assertSessionHasErrors(['razon_social', 'ruc', 'ubicacion_id']);

        $this->assertGuest();
        $this->assertSame(0, Empresa::count());
    }

    public function test_no_se_puede_registrar_como_administrador(): void
    {
        $this->post('/register', [
            'tipo_cuenta' => 'administrador',
            'nombre' => 'Intruso',
            'email' => 'intruso@correo.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('tipo_cuenta');

        $this->assertGuest();
    }

    // ---------------- Redirección al panel ----------------

    public function test_cada_rol_es_enviado_a_su_panel(): void
    {
        $casos = [
            Rol::POSTULANTE => 'postulante.inicio',
            Rol::RECLUTADOR => 'reclutador.inicio',
            Rol::SUPERVISOR => 'supervisor.inicio',
            Rol::ADMINISTRADOR => 'admin.inicio',
        ];

        foreach ($casos as $rol => $ruta) {
            $this->actingAs($this->usuarioConRol($rol))
                ->get('/panel')
                ->assertRedirect(route($ruta));
        }
    }

    // ---------------- Middleware rol ----------------

    public function test_postulante_no_puede_entrar_a_paneles_de_otros_roles(): void
    {
        $postulante = $this->usuarioConRol(Rol::POSTULANTE);

        $this->actingAs($postulante)->get(route('postulante.vacantes.index'))->assertOk();
        $this->actingAs($postulante)->get(route('reclutador.inicio'))->assertForbidden();
        $this->actingAs($postulante)->get(route('supervisor.inicio'))->assertForbidden();
        $this->actingAs($postulante)->get(route('admin.inicio'))->assertForbidden();
    }

    public function test_reclutador_ve_su_panel_pero_no_el_de_administracion(): void
    {
        $empresa = Empresa::create([
            'ubicacion_id' => Ubicacion::firstOrCreate(['departamento' => 'Lima', 'provincia' => 'Lima'])->id,
            'razon_social' => 'Andina Software S.A.C.',
            'ruc' => '20600000001',
        ]);
        $reclutador = $this->usuarioConRol(Rol::RECLUTADOR, ['empresa_id' => $empresa->id]);

        $this->actingAs($reclutador)->get(route('reclutador.inicio'))
            ->assertOk()
            ->assertSee('pendiente de verificación');
        $this->actingAs($reclutador)->get(route('admin.inicio'))->assertForbidden();
    }

    public function test_administrador_puede_entrar_al_panel_de_supervisor(): void
    {
        $admin = $this->usuarioConRol(Rol::ADMINISTRADOR);

        $this->actingAs($admin)->get(route('admin.inicio'))->assertOk();
        $this->actingAs($admin)->get(route('supervisor.empresas.index'))->assertOk();
    }

    public function test_supervisor_no_puede_entrar_al_panel_de_administrador(): void
    {
        $supervisor = $this->usuarioConRol(Rol::SUPERVISOR);

        $this->actingAs($supervisor)->get(route('supervisor.empresas.index'))->assertOk();
        $this->actingAs($supervisor)->get(route('admin.inicio'))->assertForbidden();
    }

    public function test_invitado_es_enviado_al_login(): void
    {
        $this->get(route('postulante.inicio'))->assertRedirect(route('login'));
    }

    // ---------------- Usuarios desactivados ----------------

    public function test_usuario_desactivado_no_puede_iniciar_sesion(): void
    {
        $usuario = $this->usuarioConRol(Rol::POSTULANTE, ['activo' => false]);

        $this->post('/login', ['email' => $usuario->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_usuario_desactivado_con_sesion_abierta_es_expulsado(): void
    {
        $usuario = $this->usuarioConRol(Rol::POSTULANTE);
        $this->actingAs($usuario);

        $usuario->update(['activo' => false]);

        $this->get(route('postulante.inicio'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
