<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Habilidad;
use App\Models\Postulacion;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostulanteTest extends TestCase
{
    use RefreshDatabase;

    private User $postulante;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postulante = User::factory()->postulante()->create();
    }

    private function vacanteConLaravel(array $extra = []): Vacante
    {
        $vacante = Vacante::factory()->create($extra);
        $vacante->habilidades()->attach(Habilidad::where('nombre', 'Laravel')->value('id'), ['obligatoria' => true]);

        return $vacante;
    }

    // ---------------- Buscador ----------------

    public function test_buscador_muestra_vacantes_publicadas_ordenadas_por_match(): void
    {
        $this->postulante->perfilPostulante->habilidades()->attach(Habilidad::where('nombre', 'Laravel')->value('id'), ['nivel' => 4]);

        $conMatch = $this->vacanteConLaravel(['titulo' => 'Vacante Que Coincide', 'publicada_en' => now()->subMonth()]);
        $sinMatch = Vacante::factory()->create(['titulo' => 'Vacante Sin Coincidencia', 'publicada_en' => now()]);
        $sinMatch->habilidades()->attach(Habilidad::where('nombre', 'Python')->value('id'), ['obligatoria' => true]);

        $respuesta = $this->actingAs($this->postulante)->get(route('postulante.vacantes.index'));

        $respuesta->assertOk()->assertSeeInOrder(['Vacante Que Coincide', 'Vacante Sin Coincidencia']);
    }

    public function test_buscador_oculta_borradores_cerradas_y_empresas_no_verificadas(): void
    {
        Vacante::factory()->create(['titulo' => 'Visible']);
        Vacante::factory()->borrador()->create(['titulo' => 'Es Borrador']);
        Vacante::factory()->cerrada()->create(['titulo' => 'Esta Cerrada']);
        Vacante::factory()->create(['titulo' => 'De Empresa Pendiente', 'empresa_id' => Empresa::factory()->pendiente()]);

        $this->actingAs($this->postulante)->get(route('postulante.vacantes.index'))
            ->assertSee('Visible')
            ->assertDontSee('Es Borrador')
            ->assertDontSee('Esta Cerrada')
            ->assertDontSee('De Empresa Pendiente');
    }

    public function test_filtros_por_texto_y_modalidad(): void
    {
        Vacante::factory()->create(['titulo' => 'Analista Remoto', 'modalidad' => 'remoto']);
        Vacante::factory()->create(['titulo' => 'Analista Presencial', 'modalidad' => 'presencial']);
        Vacante::factory()->create(['titulo' => 'Otro Puesto', 'modalidad' => 'remoto']);

        $this->actingAs($this->postulante)
            ->get(route('postulante.vacantes.index', ['q' => 'analista', 'modalidad' => ['remoto']]))
            ->assertSee('Analista Remoto')
            ->assertDontSee('Analista Presencial')
            ->assertDontSee('Otro Puesto');
    }

    // ---------------- Detalle y postulación ----------------

    public function test_detalle_muestra_el_porcentaje_de_match(): void
    {
        $vacante = $this->vacanteConLaravel();

        $this->actingAs($this->postulante)
            ->get(route('postulante.vacantes.show', $vacante))
            ->assertOk()
            ->assertSee($vacante->titulo)
            ->assertSee('por ciento de compatibilidad')
            ->assertSee('Laravel');
    }

    public function test_postular_guarda_el_match_y_el_trigger_registra_el_historial(): void
    {
        $vacante = $this->vacanteConLaravel(['modalidad' => 'remoto']);
        $this->postulante->perfilPostulante->habilidades()->attach(Habilidad::where('nombre', 'Laravel')->value('id'), ['nivel' => 4]);

        $this->actingAs($this->postulante)
            ->post(route('postulante.vacantes.postular', $vacante), ['carta_presentacion' => 'Me interesa mucho.'])
            ->assertRedirect(route('postulante.postulaciones.index'));

        $postulacion = Postulacion::first();
        $this->assertSame('postulado', $postulacion->estado);
        $this->assertEquals(100, $postulacion->porcentaje_match); // 70 + 20 (sin deseables) + 10 (remoto)
        $this->assertCount(1, $postulacion->historial);

        $this->actingAs($this->postulante)
            ->get(route('postulante.postulaciones.show', $postulacion))
            ->assertOk()
            ->assertSee('Postulaste a la vacante');
    }

    public function test_no_se_puede_postular_dos_veces(): void
    {
        $vacante = $this->vacanteConLaravel();

        $this->actingAs($this->postulante)->post(route('postulante.vacantes.postular', $vacante));
        $this->actingAs($this->postulante)->post(route('postulante.vacantes.postular', $vacante))->assertSessionHas('error');

        $this->assertSame(1, Postulacion::count());
    }

    public function test_no_se_puede_postular_a_una_vacante_cerrada(): void
    {
        $vacante = Vacante::factory()->cerrada()->create();

        $this->actingAs($this->postulante)->post(route('postulante.vacantes.postular', $vacante))->assertStatus(422);
        $this->assertSame(0, Postulacion::count());
    }

    public function test_postulante_no_ve_postulaciones_ajenas(): void
    {
        $ajena = Postulacion::create([
            'vacante_id' => Vacante::factory()->create()->id,
            'perfil_postulante_id' => User::factory()->postulante()->create()->perfilPostulante->id,
        ]);

        $this->actingAs($this->postulante)->get(route('postulante.postulaciones.show', $ajena))->assertForbidden();
    }

    public function test_guardar_y_quitar_de_favoritos(): void
    {
        $vacante = Vacante::factory()->create(['titulo' => 'Mi Favorita']);

        $this->actingAs($this->postulante)->post(route('postulante.vacantes.guardar', $vacante));
        $this->actingAs($this->postulante)->get(route('postulante.guardados.index'))->assertSee('Mi Favorita');

        $this->actingAs($this->postulante)->post(route('postulante.vacantes.guardar', $vacante));
        $this->assertSame(0, $this->postulante->perfilPostulante->vacantesGuardadas()->count());
    }

    // ---------------- Perfil ----------------

    public function test_actualizar_perfil_y_subir_cv(): void
    {
        Storage::fake('local');

        $this->actingAs($this->postulante)->patch(route('postulante.perfil.update'), [
            'titular' => 'Desarrollador Laravel',
            'ubicacion_id' => Ubicacion::value('id'),
            'anios_experiencia' => 2,
            'cv' => UploadedFile::fake()->create('mi-cv.pdf', 300, 'application/pdf'),
        ])->assertSessionHas('exito');

        $perfil = $this->postulante->perfilPostulante->fresh();
        $this->assertSame('Desarrollador Laravel', $perfil->titular);
        Storage::disk('local')->assertExists($perfil->cv_ruta);

        // El dueño puede ver su CV; otro postulante no
        $this->actingAs($this->postulante)->get(route('cv.show', $perfil))->assertOk();
        $this->actingAs(User::factory()->postulante()->create())->get(route('cv.show', $perfil))->assertForbidden();
    }

    public function test_el_cv_solo_acepta_pdf(): void
    {
        $this->actingAs($this->postulante)->patch(route('postulante.perfil.update'), [
            'titular' => 'X',
            'ubicacion_id' => Ubicacion::value('id'),
            'anios_experiencia' => 0,
            'cv' => UploadedFile::fake()->create('virus.exe', 10),
        ])->assertSessionHasErrors('cv');
    }

    public function test_reclutador_ve_el_cv_solo_si_postulo_a_su_empresa(): void
    {
        Storage::fake('local');
        $perfil = $this->postulante->perfilPostulante;
        $perfil->update(['cv_ruta' => UploadedFile::fake()->create('cv.pdf', 10)->store('cvs', 'local')]);

        $vacante = Vacante::factory()->create();
        $reclutadorDeLaEmpresa = User::factory()->reclutador($vacante->empresa)->create();
        $otroReclutador = User::factory()->reclutador()->create();

        $this->actingAs($reclutadorDeLaEmpresa)->get(route('cv.show', $perfil))->assertForbidden();

        $perfil->postulaciones()->create(['vacante_id' => $vacante->id]);

        $this->actingAs($reclutadorDeLaEmpresa)->get(route('cv.show', $perfil))->assertOk();
        $this->actingAs($otroReclutador)->get(route('cv.show', $perfil))->assertForbidden();
    }

    public function test_guardar_habilidades_con_nivel(): void
    {
        $laravel = Habilidad::where('nombre', 'Laravel')->value('id');
        $sql = Habilidad::where('nombre', 'SQL')->value('id');

        $this->actingAs($this->postulante)
            ->put(route('postulante.perfil.habilidades'), ['habilidades' => [$laravel => 4, $sql => 2]])
            ->assertSessionHas('exito');

        $habilidades = $this->postulante->perfilPostulante->habilidades;
        $this->assertCount(2, $habilidades);
        $this->assertSame(4, (int) $habilidades->firstWhere('id', $laravel)->pivot->nivel);
    }

    public function test_agregar_y_eliminar_experiencia(): void
    {
        $this->actingAs($this->postulante)->post(route('postulante.perfil.experiencias.store'), [
            'empresa_nombre' => 'Andina Software',
            'cargo' => 'Practicante',
            'fecha_inicio' => '2025-01-01',
        ]);

        $experiencia = $this->postulante->perfilPostulante->experiencias()->first();
        $this->assertSame('Practicante', $experiencia->cargo);

        $this->actingAs($this->postulante)->get(route('postulante.perfil.edit'))->assertOk()->assertSee('Andina Software');

        $this->actingAs($this->postulante)->delete(route('postulante.perfil.experiencias.destroy', $experiencia));
        $this->assertSame(0, $this->postulante->perfilPostulante->experiencias()->count());
    }
}
