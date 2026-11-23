<?php

namespace App\Services;

use App\Models\PerfilPostulante;
use App\Models\Vacante;
use Illuminate\Database\Eloquent\Builder;

/**
 * Calcula el porcentaje de compatibilidad (match) entre un postulante y una vacante.
 *
 *   habilidades obligatorias que tiene el postulante  × 70 %
 *   habilidades deseables que tiene el postulante     × 20 %
 *   misma región (departamento) o vacante remota      × 10 %
 *
 * Si la vacante no pide habilidades de un tipo, esa parte cuenta completa.
 *
 * Hay dos implementaciones de la MISMA fórmula:
 *  - calcular(): en PHP, devuelve además el detalle (para la pantalla de detalle y al postular).
 *  - agregarPorcentaje(): en SQL, para ordenar el buscador por compatibilidad.
 */
class MatchingService
{
    public const PESO_OBLIGATORIAS = 70;
    public const PESO_DESEABLES = 20;
    public const PESO_UBICACION = 10;

    /**
     * @return array{porcentaje: float, obligatorias_total: int, obligatorias_tiene: int,
     *               deseables_total: int, deseables_tiene: int, ubicacion_ok: bool,
     *               habilidades: \Illuminate\Support\Collection}
     */
    public function calcular(PerfilPostulante $perfil, Vacante $vacante): array
    {
        $idsPerfil = $perfil->habilidades()->pluck('habilidades.id')->all();
        $vacante->loadMissing('habilidades', 'ubicacion');
        $perfil->loadMissing('ubicacion');

        $habilidades = $vacante->habilidades
            ->map(fn ($h) => [
                'nombre' => $h->nombre,
                'obligatoria' => (bool) $h->pivot->obligatoria,
                'tiene' => in_array($h->id, $idsPerfil, true),
            ])
            ->sortByDesc(fn ($h) => [$h['obligatoria'], $h['tiene']])
            ->values();

        $obligatorias = $habilidades->where('obligatoria', true);
        $deseables = $habilidades->where('obligatoria', false);

        $ubicacionOk = $vacante->modalidad === 'remoto'
            || ($perfil->ubicacion && $perfil->ubicacion->departamento === $vacante->ubicacion?->departamento);

        $porcentaje = self::PESO_OBLIGATORIAS * $this->proporcion($obligatorias->where('tiene', true)->count(), $obligatorias->count())
            + self::PESO_DESEABLES * $this->proporcion($deseables->where('tiene', true)->count(), $deseables->count())
            + ($ubicacionOk ? self::PESO_UBICACION : 0);

        return [
            'porcentaje' => round($porcentaje, 2),
            'obligatorias_total' => $obligatorias->count(),
            'obligatorias_tiene' => $obligatorias->where('tiene', true)->count(),
            'deseables_total' => $deseables->count(),
            'deseables_tiene' => $deseables->where('tiene', true)->count(),
            'ubicacion_ok' => $ubicacionOk,
            'habilidades' => $habilidades,
        ];
    }

    /**
     * Agrega la columna calculada "porcentaje_match" a una consulta de vacantes (en SQL),
     * para poder ordenar y mostrar la compatibilidad en el buscador.
     */
    public function agregarPorcentaje(Builder $consulta, PerfilPostulante $perfil): Builder
    {
        // Arreglo de PostgreSQL con las habilidades del postulante: '{3,15,42}'
        $ids = '{'.$perfil->habilidades()->pluck('habilidades.id')->implode(',').'}';
        $departamento = $perfil->ubicacion?->departamento;

        $proporcion = fn (string $condicion) => "COALESCE((
            SELECT COUNT(*) FILTER (WHERE vh.habilidad_id = ANY(?::bigint[]))::numeric / NULLIF(COUNT(*), 0)
            FROM vacante_habilidad vh
            WHERE vh.vacante_id = vacantes.id AND {$condicion}
        ), 1)";

        $sql = 'ROUND('
            .self::PESO_OBLIGATORIAS.' * '.$proporcion('vh.obligatoria')
            .' + '.self::PESO_DESEABLES.' * '.$proporcion('NOT vh.obligatoria')
            .' + '.self::PESO_UBICACION.' * (CASE WHEN vacantes.modalidad = \'remoto\'
                    OR EXISTS (SELECT 1 FROM ubicaciones u WHERE u.id = vacantes.ubicacion_id AND u.departamento = ?)
                  THEN 1 ELSE 0 END)'
            .', 2) AS porcentaje_match';

        if (is_null($consulta->getQuery()->columns)) {
            $consulta->select('vacantes.*');
        }

        return $consulta->selectRaw($sql, [$ids, $ids, $departamento]);
    }

    private function proporcion(int $tiene, int $total): float
    {
        return $total === 0 ? 1.0 : $tiene / $total;
    }
}
