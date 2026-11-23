<?php

namespace Database\Seeders;

use App\Models\Entrevista;
use App\Models\PerfilPostulante;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use App\Services\MatchingService;
use Database\Seeders\Datos\Textos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Nivel 6 (último): postulaciones y entrevistas.
 * Depende de: vacantes y postulantes.
 *
 * - El % de match se calcula con MatchingService (la misma fórmula de la aplicación).
 * - Los cambios de estado pasan por los triggers: se llena historial_postulaciones
 *   y las vacantes se cierran solas al cubrir sus puestos.
 */
class PostulacionSeeder extends Seeder
{
    /** Candidatos por vacante (promedio ≈ 8 → unas 3 700 postulaciones). */
    public const MIN_POR_VACANTE = 3;
    public const MAX_POR_VACANTE = 13;

    private array $pares = [];

    public function run(MatchingService $matching): void
    {
        // Se cargan en memoria las habilidades y la región de cada postulante (una sola consulta)
        $perfiles = PerfilPostulante::with(['habilidades:id', 'ubicacion:id,departamento'])->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'habilidades' => $p->habilidades->pluck('id')->all(),
                'departamento' => $p->ubicacion?->departamento,
            ]);

        $vacantes = Vacante::whereIn('estado', ['publicada', 'cerrada'])
            ->with(['habilidades', 'ubicacion', 'empresa'])
            ->get();

        $demo = User::where('email', 'postulante@jobmatch.test')->first()?->perfilPostulante;

        DB::transaction(function () use ($matching, $perfiles, $vacantes, $demo) {
            if ($demo) {
                $this->postulacionesDelDemo($matching, $demo, $vacantes);
            }

            foreach ($vacantes as $vacante) {
                // Se evalúan 40 postulantes al azar y postulan los más compatibles + algunos al azar
                $evaluados = $perfiles->random(40)->map(fn ($p) => $p + [
                    'match' => $matching->calcularConDatos($p['habilidades'], $p['departamento'], $vacante)['porcentaje'],
                ])->sortByDesc('match')->values();

                $cantidad = rand(self::MIN_POR_VACANTE, self::MAX_POR_VACANTE);
                $elegidos = $evaluados->take((int) ceil($cantidad * 0.7))
                    ->merge($evaluados->slice(10)->random(min(30, (int) floor($cantidad * 0.3))));

                foreach ($elegidos as $candidato) {
                    $this->postular($vacante, $candidato['id'], $candidato['match']);
                }
            }

            // El trigger guarda el historial con la fecha actual: se ajusta a fechas coherentes
            DB::statement(<<<'SQL'
                UPDATE historial_postulaciones h
                SET cambiado_en = LEAST(
                    p.created_at + CASE WHEN h.estado_anterior IS NULL THEN INTERVAL '0' ELSE (1 + random() * 12) * INTERVAL '1 day' END,
                    NOW()
                )
                FROM postulaciones p
                WHERE p.id = h.postulacion_id
            SQL);
        });
    }

    /** Crea la postulación y la avanza a un estado al azar (pasando por los triggers). */
    private function postular(Vacante $vacante, int $perfilId, float $match, ?string $estado = null): ?Postulacion
    {
        if (isset($this->pares[$vacante->id][$perfilId])) {
            return null;
        }
        $this->pares[$vacante->id][$perfilId] = true;

        $fecha = Carbon::instance(fake()->dateTimeBetween($vacante->publicada_en, min(now(), $vacante->cierra_en ?? now())));

        $postulacion = new Postulacion([
            'vacante_id' => $vacante->id,
            'perfil_postulante_id' => $perfilId,
            'carta_presentacion' => fake()->boolean(35) ? Arr::random(Textos::CARTAS) : null,
            'porcentaje_match' => $match,
        ]);
        $postulacion->created_at = $fecha;
        $postulacion->updated_at = $fecha;
        $postulacion->save(); // trigger: historial "postulado"

        // Los de mayor match avanzan más en el proceso
        $estado ??= match (true) {
            $match >= 70 => Arr::random(['en_revision', 'entrevista', 'entrevista', 'contratado', 'postulado']),
            $match >= 40 => Arr::random(['postulado', 'en_revision', 'en_revision', 'entrevista', 'rechazado']),
            default => Arr::random(['postulado', 'postulado', 'rechazado', 'rechazado']),
        };

        // No se contrata más gente que puestos tiene la vacante
        if ($estado === 'contratado' && $vacante->postulaciones()->where('estado', 'contratado')->count() >= $vacante->puestos) {
            $estado = 'entrevista';
        }

        if ($estado !== 'postulado') {
            if (in_array($estado, ['entrevista', 'contratado'], true)) {
                $postulacion->update(['estado' => 'en_revision']);
            }
            $postulacion->update(['estado' => $estado]); // triggers: historial y cierre de vacante
        }

        $this->crearEntrevistas($postulacion, $estado);

        return $postulacion;
    }

    private function crearEntrevistas(Postulacion $postulacion, string $estado): void
    {
        if ($estado === 'entrevista') {
            Entrevista::factory()->create(['postulacion_id' => $postulacion->id]); // próxima, programada
        } elseif ($estado === 'contratado' || ($estado === 'rechazado' && fake()->boolean(40))) {
            Entrevista::factory()->realizada()->create(['postulacion_id' => $postulacion->id]);
        }
    }

    /** El postulante de demostración postula a vacantes de Inka Fintech y de otras empresas. */
    private function postulacionesDelDemo(MatchingService $matching, PerfilPostulante $demo, $vacantes): void
    {
        $ids = $demo->habilidades()->pluck('habilidades.id')->all();
        $publicadas = $vacantes->where('estado', 'publicada');
        $inka = $publicadas->filter(fn ($v) => $v->empresa->ruc === '20512345678')->values();
        $otras = $publicadas->reject(fn ($v) => $v->empresa->ruc === '20512345678')->random(3)->values();

        $plan = [
            [$inka->get(0), 'entrevista'],
            [$inka->get(1), 'en_revision'],
            [$otras->get(0), 'postulado'],
            [$otras->get(1), 'rechazado'],
            [$otras->get(2), 'en_revision'],
        ];

        foreach ($plan as [$vacante, $estado]) {
            if ($vacante) {
                $this->postular($vacante, $demo->id, $matching->calcularConDatos($ids, 'Lima', $vacante)['porcentaje'], $estado);
            }
        }

        // Su entrevista será en 2 días a las 10:00 (para verla en "Próxima entrevista")
        Entrevista::whereHas('postulacion', fn ($q) => $q->where('perfil_postulante_id', $demo->id))
            ->where('estado', 'programada')
            ->update(['fecha_hora' => now()->addDays(2)->setTime(10, 0)]);

        // Una vacante guardada en favoritos
        $demo->vacantesGuardadas()->attach($publicadas->random()->id);
    }
}
