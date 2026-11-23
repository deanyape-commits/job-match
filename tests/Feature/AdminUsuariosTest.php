<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUsuariosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->administrador()->create();
    }

    private function rol(string $slug): int
    {
        return Rol::where('slug', $slug)->value('id');
    }

    public function test_resumen_y_listado_de_usuarios(): void
    {
        User::factory()->postulante()->create(['nombre' => 'Postulante Visible']);

        $this->actingAs($this->admin)->get(route('admin.inicio'))->assertOk()->assertSee('Resumen de la plataforma');
        $this->actingAs($this->admin)->get(route('admin.usuarios.index'))->assertOk()->assertSee('Postulante Visible');
    }

    public function test_filtrar_por_rol_y_buscar(): void
    {
        User::factory()->supervisor()->create(['nombre' => 'Sofía Supervisora']);
        User::factory()->postulante()->create(['nombre' => 'Pedro Postulante']);

        $this->actingAs($this->admin)->get(route('admin.usuarios.index', ['rol' => 'supervisor']))
            ->assertSee('Sofía Supervisora')->assertDontSee('Pedro Postulante');

        $this->actingAs($this->admin)->get(route('admin.usuarios.index', ['q' => 'pedro']))
            ->assertSee('Pedro Postulante')->assertDontSee('Sofía Supervisora');
    }

    public function test_crear_un_supervisor(): void
    {
        $this->actingAs($this->admin)->post(route('admin.usuarios.store'), [
            'nombre' => 'Nuevo Supervisor',
            'email' => 'nuevo@jobmatch.com',
            'rol_id' => $this->rol(Rol::SUPERVISOR),
            'activo' => 1,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.usuarios.index'));

        $usuario = User::where('email', 'nuevo@jobmatch.com')->first();
        $this->assertTrue($usuario->tieneRol(Rol::SUPERVISOR));
        $this->assertTrue(Hash::check('password123', $usuario->password)); // guardada con bcrypt
    }

    public function test_un_reclutador_necesita_empresa(): void
    {
        $this->actingAs($this->admin)->post(route('admin.usuarios.store'), [
            'nombre' => 'Sin Empresa',
            'email' => 'sin@empresa.test',
            'rol_id' => $this->rol(Rol::RECLUTADOR),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('empresa_id');
    }

    public function test_cambiar_a_postulante_crea_su_perfil_y_quita_la_empresa(): void
    {
        $reclutador = User::factory()->reclutador()->create();

        $this->actingAs($this->admin)->put(route('admin.usuarios.update', $reclutador), [
            'nombre' => $reclutador->nombre,
            'email' => $reclutador->email,
            'rol_id' => $this->rol(Rol::POSTULANTE),
            'empresa_id' => $reclutador->empresa_id,
            'activo' => 1,
        ])->assertRedirect(route('admin.usuarios.index'));

        $reclutador->refresh();
        $this->assertTrue($reclutador->tieneRol(Rol::POSTULANTE));
        $this->assertNull($reclutador->empresa_id);
        $this->assertNotNull($reclutador->perfilPostulante);
    }

    public function test_editar_sin_contrasena_mantiene_la_actual(): void
    {
        $usuario = User::factory()->postulante()->create();
        $hashAnterior = $usuario->password;

        $this->actingAs($this->admin)->put(route('admin.usuarios.update', $usuario), [
            'nombre' => 'Nombre Cambiado',
            'email' => $usuario->email,
            'rol_id' => $usuario->rol_id,
            'activo' => 1,
        ]);

        $usuario->refresh();
        $this->assertSame('Nombre Cambiado', $usuario->nombre);
        $this->assertSame($hashAnterior, $usuario->password);
    }

    public function test_desactivar_y_activar_usuario(): void
    {
        $usuario = User::factory()->postulante()->create();

        $this->actingAs($this->admin)->patch(route('admin.usuarios.activo', $usuario));
        $this->assertFalse($usuario->fresh()->activo);

        $this->actingAs($this->admin)->patch(route('admin.usuarios.activo', $usuario));
        $this->assertTrue($usuario->fresh()->activo);
    }

    public function test_el_admin_no_puede_desactivarse_ni_quitarse_el_rol(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.usuarios.activo', $this->admin))->assertSessionHas('error');
        $this->assertTrue($this->admin->fresh()->activo);

        $this->actingAs($this->admin)->put(route('admin.usuarios.update', $this->admin), [
            'nombre' => $this->admin->nombre,
            'email' => $this->admin->email,
            'rol_id' => $this->rol(Rol::POSTULANTE),
            'activo' => 0,
        ]);

        $this->admin->refresh();
        $this->assertTrue($this->admin->tieneRol(Rol::ADMINISTRADOR));
        $this->assertTrue($this->admin->activo);
    }

    public function test_supervisor_no_puede_gestionar_usuarios(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $this->actingAs($supervisor)->get(route('admin.usuarios.index'))->assertForbidden();
        $this->actingAs($supervisor)->patch(route('admin.usuarios.activo', $this->admin))->assertForbidden();
    }

    public function test_formularios_se_muestran(): void
    {
        $this->actingAs($this->admin)->get(route('admin.usuarios.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.usuarios.edit', User::factory()->reclutador(Empresa::factory()->create())->create()))->assertOk();
    }
}
