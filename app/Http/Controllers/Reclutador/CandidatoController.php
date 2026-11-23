<?php

namespace App\Http\Controllers\Reclutador;

use App\Http\Controllers\Controller;
use App\Models\Postulacion;
use App\Models\Vacante;
use App\Notifications\EstadoPostulacionCambiado;
use App\Services\Notificador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * El reclutador revisa a los candidatos de sus vacantes y cambia el estado de cada postulación.
 */
class CandidatoController extends Controller
{
    /** Candidatos de una vacante, ordenados por porcentaje de match. */
    public function index(Request $request, Vacante $vacante): View
    {
        Gate::authorize('update', $vacante);

        $estado = in_array($request->query('estado'), Postulacion::ESTADOS, true) ? $request->query('estado') : null;

        $postulaciones = $vacante->postulaciones()
            ->with(['perfilPostulante.usuario', 'perfilPostulante.ubicacion', 'perfilPostulante.habilidades'])
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->orderByDesc('porcentaje_match')
            ->orderBy('created_at')
            ->paginate(15)
            ->withQueryString();

        $totales = $vacante->postulaciones()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('reclutador.candidatos.index', [
            'vacante' => $vacante->load('habilidades'),
            'postulaciones' => $postulaciones,
            'estado' => $estado,
            'totales' => $totales,
        ]);
    }

    /** Perfil completo de un candidato para una postulación. */
    public function show(Postulacion $postulacion): View
    {
        Gate::authorize('gestionar', $postulacion);

        $postulacion->load([
            'vacante' => fn ($q) => $q->withTrashed()->with('habilidades'),
            'perfilPostulante.usuario',
            'perfilPostulante.ubicacion',
            'perfilPostulante.habilidades',
            'perfilPostulante.experiencias',
            'perfilPostulante.estudios',
            'historial',
            'entrevistas' => fn ($q) => $q->orderByDesc('fecha_hora'),
        ]);

        return view('reclutador.candidatos.show', compact('postulacion'));
    }

    /**
     * Cambiar el estado de la postulación.
     * Si pasa a "contratado", el trigger trg_actualizar_estado_vacante cierra la vacante
     * cuando se cubren todos los puestos, y trg_historial_postulacion registra el cambio.
     */
    public function cambiarEstado(Request $request, Postulacion $postulacion, Notificador $notificador): RedirectResponse|JsonResponse
    {
        Gate::authorize('gestionar', $postulacion);

        $request->validate([
            'estado' => ['required', Rule::in(Postulacion::ESTADOS)],
        ]);

        if ($request->estado === 'contratado' && $postulacion->estado !== 'contratado') {
            $vacante = $postulacion->vacante;
            $contratados = $vacante->postulaciones()->where('estado', 'contratado')->count();
            if ($contratados >= $vacante->puestos) {
                $error = 'Ya se cubrieron todos los puestos de esta vacante.';

                return $request->expectsJson()
                    ? response()->json(['mensaje' => $error], 422)
                    : back()->with('error', $error);
            }
        }

        $estadoAnterior = $postulacion->estado;
        $postulacion->update(['estado' => $request->estado]);

        // Aviso al postulante por correo y campanita (solo si el estado cambió)
        if ($estadoAnterior !== $request->estado) {
            $notificador->enviar($postulacion->perfilPostulante->usuario, new EstadoPostulacionCambiado($postulacion));
        }

        $nombre = $postulacion->perfilPostulante->usuario->nombre;
        $mensaje = "Estado de {$nombre}: ".Postulacion::ESTADOS_TEXTO[$request->estado].'.';
        $vacanteCerrada = $postulacion->vacante->fresh()->estado === 'cerrada' && $request->estado === 'contratado';
        if ($vacanteCerrada) {
            $mensaje .= ' Se cubrieron todos los puestos: la vacante se cerró automáticamente.';
        }

        // Desde JavaScript (sin recargar la página) se responde en JSON
        if ($request->expectsJson()) {
            return response()->json([
                'estado' => $request->estado,
                'mensaje' => $mensaje,
                'vacante_cerrada' => $vacanteCerrada,
            ]);
        }

        return back()->with('exito', $mensaje);
    }
}
