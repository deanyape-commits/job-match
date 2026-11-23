<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\PerfilPostulante;
use App\Models\Postulacion;
use App\Models\Rol;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Comprueba los triggers y el procedimiento almacenado de PostgreSQL.
 */
class BaseDeDatosTest extends TestCase
{
    use RefreshDatabase;

    private Vacante $vacante;

    protected function setUp(): void
    {
        parent::setUp();

        $ubicacion = Ubicacion::create(['departamento' => 'Lima', 'provincia' => 'Lima']);
        $categoria = Categoria::create(['nombre' => 'Desarrollo de software', 'slug' => 'desarrollo-de-software']);

        $empresa = Empresa::create([
            'ubicacion_id' => $ubicacion->id,
            'razon_social' => 'Empresa de Prueba S.A.C.',
            'ruc' => '20123456789',
            'estado' => 'verificada',
        ]);

        $reclutador = User::factory()->create([
            'rol_id' => Rol::where('slug', Rol::RECLUTADOR)->value('id'),
            'empresa_id' => $empresa->id,
        ]);

        $this->vacante = Vacante::create([
            'empresa_id' => $empresa->id,
            'reclutador_id' => $reclutador->id,
            'categoria_id' => $categoria->id,
            'ubicacion_id' => $ubicacion->id,
            'titulo' => 'Desarrollador Laravel',
            'slug' => 'desarrollador-laravel',
            'descripcion' => 'Descripción',
            'requisitos' => 'Requisitos',
            'modalidad' => 'remoto',
            'tipo_contrato' => 'tiempo_completo',
            'nivel_experiencia' => 'junior',
            'puestos' => 1,
            'estado' => 'publicada',
            'publicada_en' => now(),
        ]);
    }

    private function crearPostulacion(): Postulacion
    {
        $perfil = PerfilPostulante::create(['usuario_id' => User::factory()->create()->id]);

        return Postulacion::create([
            'vacante_id' => $this->vacante->id,
            'perfil_postulante_id' => $perfil->id,
            'porcentaje_match' => 80,
        ]);
    }

    public function test_trigger_historial_registra_la_creacion_y_cada_cambio_de_estado(): void
    {
        $postulacion = $this->crearPostulacion();
        $postulacion->update(['estado' => 'en_revision']);
        $postulacion->update(['estado' => 'entrevista']);

        $historial = $postulacion->historial()->get();

        $this->assertCount(3, $historial);
        $this->assertNull($historial[0]->estado_anterior);
        $this->assertSame('postulado', $historial[0]->estado_nuevo);
        $this->assertSame('en_revision', $historial[2]->estado_anterior);
        $this->assertSame('entrevista', $historial[2]->estado_nuevo);
    }

    public function test_trigger_cierra_la_vacante_al_cubrir_todos_los_puestos(): void
    {
        $postulacion = $this->crearPostulacion();
        $this->assertSame('publicada', $this->vacante->fresh()->estado);

        $postulacion->update(['estado' => 'contratado']);

        $this->assertSame('cerrada', $this->vacante->fresh()->estado);
    }

    public function test_trigger_no_cierra_la_vacante_si_quedan_puestos(): void
    {
        $this->vacante->update(['puestos' => 2]);
        $postulacion = $this->crearPostulacion();

        $postulacion->update(['estado' => 'contratado']);

        $this->assertSame('publicada', $this->vacante->fresh()->estado);
    }

    public function test_procedimiento_reporte_empresa_devuelve_los_totales(): void
    {
        $this->crearPostulacion()->update(['estado' => 'contratado']);
        $this->crearPostulacion();

        $reporte = DB::selectOne(
            'SELECT * FROM sp_reporte_empresa(?, ?, ?)',
            [$this->vacante->empresa_id, now()->startOfYear()->toDateString(), now()->toDateString()]
        );

        $this->assertEquals(1, $reporte->vacantes_publicadas);
        $this->assertEquals(2, $reporte->total_postulaciones);
        $this->assertEquals(1, $reporte->total_contratados);
        $this->assertEquals(80, $reporte->promedio_match);
    }
}
