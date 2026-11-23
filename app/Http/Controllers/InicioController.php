<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\View\View;

/** Página de inicio pública (landing). */
class InicioController extends Controller
{
    public function __invoke(): View
    {
        $visibles = Vacante::publicadas()->whereHas('empresa', fn ($q) => $q->where('estado', 'verificada'));

        return view('inicio', [
            'recientes' => (clone $visibles)->with(['empresa', 'ubicacion'])->latest('publicada_en')->limit(6)->get(),
            'cifras' => [
                'vacantes' => (clone $visibles)->count(),
                'empresas' => Empresa::where('estado', 'verificada')->count(),
                'contratados' => Postulacion::where('estado', 'contratado')->count(),
            ],
            'populares' => ['Laravel', 'Python', 'Soporte TI', 'Power BI', 'React'],

            // Texto rotativo: las áreas con más vacantes publicadas
            'areas' => Categoria::withCount(['vacantes' => fn ($q) => $q->where('estado', 'publicada')])
                ->orderByDesc('vacantes_count')
                ->limit(6)
                ->pluck('nombre'),

            // Carrusel: empresas verificadas con más vacantes activas
            'empresas' => Empresa::where('estado', 'verificada')
                ->withCount(['vacantes' => fn ($q) => $q->where('estado', 'publicada')])
                ->orderByDesc('vacantes_count')
                ->limit(14)
                ->get(['id', 'razon_social']),
        ]);
    }
}
