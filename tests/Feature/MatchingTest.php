<?php

namespace Tests\Feature;

use App\Models\Habilidad;
use App\Models\PerfilPostulante;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\Vacante;
use App\Services\MatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchingTest extends TestCase
{
    use RefreshDatabase;

    private MatchingService $matching;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matching = new MatchingService;
    }

    private function id(string $nombre): int
    {
        return Habilidad::where('nombre', $nombre)->value('id');
    }

    private function ubicacion(string $departamento): int
    {
        return Ubicacion::where('departamento', $departamento)->value('id');
    }

    /** Vacante presencial en Lima: obligatorias PHP, Laravel, SQL, Git · deseables Docker, Redis */
    private function vacante(array $extra = []): Vacante
    {
        $vacante = Vacante::factory()->create($extra + ['modalidad' => 'presencial', 'ubicacion_id' => $this->ubicacion('Lima')]);
        $vacante->habilidades()->attach([
            $this->id('PHP') => ['obligatoria' => true],
            $this->id('Laravel') => ['obligatoria' => true],
            $this->id('SQL') => ['obligatoria' => true],
            $this->id('Git') => ['obligatoria' => true],
            $this->id('Docker') => ['obligatoria' => false],
            $this->id('Redis') => ['obligatoria' => false],
        ]);

        return $vacante;
    }

    private function perfil(array $habilidades, string $departamento = 'Lima'): PerfilPostulante
    {
        $perfil = User::factory()->postulante()->create()->perfilPostulante;
        $perfil->update(['ubicacion_id' => $this->ubicacion($departamento)]);
        $perfil->habilidades()->sync(collect($habilidades)->mapWithKeys(fn ($n) => [$this->id($n) => ['nivel' => 3]]));

        return $perfil;
    }

    private function porcentajeSql(PerfilPostulante $perfil, Vacante $vacante): float
    {
        return (float) $this->matching->agregarPorcentaje(Vacante::whereKey($vacante->id), $perfil)->value('porcentaje_match');
    }

    public function test_postulante_con_todas_las_habilidades_y_misma_ciudad_tiene_100(): void
    {
        $vacante = $this->vacante();
        $perfil = $this->perfil(['PHP', 'Laravel', 'SQL', 'Git', 'Docker', 'Redis']);

        $this->assertEquals(100, $this->matching->calcular($perfil, $vacante)['porcentaje']);
    }

    public function test_calculo_parcial_segun_la_formula(): void
    {
        $vacante = $this->vacante();
        // 3 de 4 obligatorias (52.5) + 1 de 2 deseables (10) + misma región (10) = 72.5
        $perfil = $this->perfil(['PHP', 'Laravel', 'SQL', 'Docker']);

        $resultado = $this->matching->calcular($perfil, $vacante);

        $this->assertEquals(72.5, $resultado['porcentaje']);
        $this->assertSame(3, $resultado['obligatorias_tiene']);
        $this->assertSame(1, $resultado['deseables_tiene']);
        $this->assertTrue($resultado['ubicacion_ok']);
    }

    public function test_otra_region_pierde_el_10_por_ciento_salvo_si_es_remoto(): void
    {
        $perfil = $this->perfil(['PHP', 'Laravel', 'SQL', 'Git', 'Docker', 'Redis'], 'Arequipa');

        $this->assertEquals(90, $this->matching->calcular($perfil, $this->vacante())['porcentaje']);
        $this->assertEquals(100, $this->matching->calcular($perfil, $this->vacante(['modalidad' => 'remoto']))['porcentaje']);
    }

    public function test_sin_habilidades_solo_suma_la_ubicacion(): void
    {
        $this->assertEquals(10, $this->matching->calcular($this->perfil([]), $this->vacante())['porcentaje']);
    }

    public function test_la_version_sql_da_el_mismo_resultado_que_la_version_php(): void
    {
        $casos = [
            [['PHP', 'Laravel', 'SQL', 'Git', 'Docker', 'Redis'], 'Lima', 'presencial'],
            [['PHP', 'Laravel', 'SQL', 'Docker'], 'Lima', 'presencial'],
            [['PHP'], 'Arequipa', 'presencial'],
            [['Docker', 'Redis'], 'Cusco', 'remoto'],
            [[], 'Lima', 'hibrido'],
        ];

        foreach ($casos as [$habilidades, $departamento, $modalidad]) {
            $vacante = $this->vacante(['modalidad' => $modalidad]);
            $perfil = $this->perfil($habilidades, $departamento);

            $this->assertEquals(
                $this->matching->calcular($perfil, $vacante)['porcentaje'],
                $this->porcentajeSql($perfil, $vacante),
                'PHP y SQL no coinciden para: '.implode(', ', $habilidades)." / $departamento / $modalidad"
            );
        }
    }
}
