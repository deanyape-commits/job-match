<?php

namespace App\Http\Controllers\Postulante;

use App\Http\Controllers\Controller;
use App\Services\MatchingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Vacantes que el postulante guardó como favoritas. */
class GuardadoController extends Controller
{
    public function __invoke(Request $request, MatchingService $matching): View
    {
        $perfil = $request->user()->perfil();

        $consulta = $perfil->vacantesGuardadas()
            ->with(['empresa', 'ubicacion'])
            ->getQuery()
            ->orderByDesc('vacantes_guardadas.created_at');

        $matching->agregarPorcentaje($consulta, $perfil);

        return view('postulante.guardados', [
            'vacantes' => $consulta->paginate(10),
            'postuladas' => $perfil->postulaciones()->pluck('vacante_id')->all(),
        ]);
    }
}
