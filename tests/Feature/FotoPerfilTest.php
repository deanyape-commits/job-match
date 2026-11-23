<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FotoPerfilTest extends TestCase
{
    use RefreshDatabase;

    /** Imagen PNG real de 1×1 píxel (no requiere la extensión GD). */
    private function png(string $nombre = 'foto.png'): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($nombre, $bytes);
    }

    public function test_todos_los_roles_pueden_subir_su_foto(): void
    {
        Storage::fake('public');

        foreach (['postulante', 'reclutador', 'supervisor', 'administrador'] as $rol) {
            $usuario = User::factory()->{$rol}()->create();

            $this->actingAs($usuario)->get(route('profile.edit'))->assertOk()->assertSee('Foto de perfil');

            $this->actingAs($usuario)
                ->post(route('profile.foto.update'), ['foto' => $this->png()])
                ->assertRedirect(route('profile.edit'))
                ->assertSessionHas('exito');

            $usuario->refresh();
            $this->assertNotNull($usuario->avatar_ruta, "Falló para el rol $rol");
            Storage::disk('public')->assertExists($usuario->avatar_ruta);
        }
    }

    public function test_la_foto_aparece_en_el_menu(): void
    {
        Storage::fake('public');
        $usuario = User::factory()->postulante()->create();
        $this->actingAs($usuario)->post(route('profile.foto.update'), ['foto' => $this->png()]);

        $this->actingAs($usuario)->get(route('postulante.vacantes.index'))
            ->assertSee($usuario->fresh()->avatar_url);
    }

    public function test_reemplazar_la_foto_borra_la_anterior(): void
    {
        Storage::fake('public');
        $usuario = User::factory()->postulante()->create();

        $this->actingAs($usuario)->post(route('profile.foto.update'), ['foto' => $this->png('primera.png')]);
        $anterior = $usuario->fresh()->avatar_ruta;

        $this->actingAs($usuario)->post(route('profile.foto.update'), ['foto' => $this->png('segunda.png')]);

        Storage::disk('public')->assertMissing($anterior);
        Storage::disk('public')->assertExists($usuario->fresh()->avatar_ruta);
    }

    public function test_quitar_la_foto(): void
    {
        Storage::fake('public');
        $usuario = User::factory()->postulante()->create();
        $this->actingAs($usuario)->post(route('profile.foto.update'), ['foto' => $this->png()]);
        $ruta = $usuario->fresh()->avatar_ruta;

        $this->actingAs($usuario)->delete(route('profile.foto.destroy'))->assertSessionHas('exito');

        $this->assertNull($usuario->fresh()->avatar_ruta);
        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_solo_acepta_imagenes(): void
    {
        Storage::fake('public');
        $usuario = User::factory()->postulante()->create();

        $this->actingAs($usuario)
            ->post(route('profile.foto.update'), ['foto' => UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrorsIn('foto', 'foto');

        $this->assertNull($usuario->fresh()->avatar_ruta);
    }

    public function test_solo_los_postulantes_pueden_eliminar_su_cuenta(): void
    {
        $reclutador = User::factory()->reclutador()->create();

        $this->actingAs($reclutador)->get(route('profile.edit'))->assertDontSee('Eliminar cuenta');
        $this->actingAs($reclutador)->delete(route('profile.destroy'), ['password' => 'password'])->assertForbidden();
        $this->assertNotNull($reclutador->fresh());
    }
}
