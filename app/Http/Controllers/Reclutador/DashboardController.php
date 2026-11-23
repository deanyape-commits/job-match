<?php

namespace App\Http\Controllers\Reclutador;

use App\Http\Controllers\Controller;
use App\Models\Entrevista;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $empresa = $request->user()->empresa;
        $desde = now()->startOfYear();
        $hasta = now();

        // Indicadores desde el PROCEDIMIENTO ALMACENADO de PostgreSQL
        $reporte = DB::selectOne(
            'SELECT * FROM sp_reporte_empresa(?, ?, ?)',
            [$empresa->id, $desde->toDateString(), $hasta->toDateString()]
        );

        // Postulaciones de los últimos 6 meses (para el gráfico de barras)
        $inicioGrafico = now()->subMonths(5)->startOfMonth();
        $porMes = Postulacion::query()
            ->join('vacantes', 'vacantes.id', '=', 'postulaciones.vacante_id')
            ->where('vacantes.empresa_id', $empresa->id)
            ->where('postulaciones.created_at', '>=', $inicioGrafico)
            ->selectRaw("to_char(postulaciones.created_at, 'YYYY-MM') as mes, count(*) as total")
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $grafico = collect(range(0, 5))->map(function ($i) use ($inicioGrafico, $porMes) {
            $mes = $inicioGrafico->copy()->addMonths($i);

            return [
                'etiqueta' => ucfirst($mes->translatedFormat('M')),
                'total' => (int) ($porMes[$mes->format('Y-m')] ?? 0),
            ];
        });

        // Próximas entrevistas (7 días)
        $entrevistas = Entrevista::with('postulacion.perfilPostulante.usuario', 'postulacion.vacante')
            ->whereHas('postulacion.vacante', fn ($q) => $q->where('empresa_id', $empresa->id))
            ->where('estado', 'programada')
            ->whereBetween('fecha_hora', [now(), now()->addDays(7)])
            ->orderBy('fecha_hora')
            ->limit(5)
            ->get();

        $vacantes = Vacante::where('empresa_id', $empresa->id)
            ->withCount([
                'postulaciones',
                'postulaciones as contratados_count' => fn ($q) => $q->where('estado', 'contratado'),
            ])
            ->latest()
            ->limit(5)
            ->get();

        return view('reclutador.dashboard', [
            'empresa' => $empresa,
            'reporte' => $reporte,
            'desde' => $desde,
            'hasta' => $hasta,
            'grafico' => $grafico,
            'maximoGrafico' => max(1, $grafico->max('total')),
            'entrevistas' => $entrevistas,
            'vacantes' => $vacantes,
            'vacantesActivas' => Vacante::where('empresa_id', $empresa->id)->where('estado', 'publicada')->count(),
        ]);
    }
}
