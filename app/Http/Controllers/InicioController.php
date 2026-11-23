<?php

namespace App\Http\Controllers;

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
        ]);
    }
}
