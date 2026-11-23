<?php

namespace App\Http\Controllers\Postulante;

use App\Http\Controllers\Controller;
use App\Models\Entrevista;
use App\Models\Postulacion;
use App\Models\Vacante;
use App\Notifications\NuevaPostulacion;
use App\Services\MatchingService;
use App\Services\Notificador;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostulacionController extends Controller
{
    public function index(Request $request): View
    {
        $perfil = $request->user()->perfil();

        $postulaciones = $perfil->postulaciones()
            ->with(['vacante' => fn ($q) => $q->withTrashed()->with('empresa')])
            ->latest()
            ->paginate(15);

        $proximaEntrevista = Entrevista::with('postulacion.vacante.empresa')
            ->whereHas('postulacion', fn ($q) => $q->where('perfil_postulante_id', $perfil->id))
            ->where('estado', 'programada')
            ->where('fecha_hora', '>=', now()->subHour())
            ->orderBy('fecha_hora')
            ->first();

        $todas = $perfil->postulaciones();

        return view('postulante.postulaciones.index', [
            'postulaciones' => $postulaciones,
            'proximaEntrevista' => $proximaEntrevista,
            'estadisticas' => [
                'Postulaciones activas' => (clone $todas)->whereNotIn('estado', ['rechazado', 'contratado'])->count(),
                'En revisión' => (clone $todas)->where('estado', 'en_revision')->count(),
                'Entrevistas' => (clone $todas)->where('estado', 'entrevista')->count(),
                'Match promedio' => round((float) (clone $todas)->avg('porcentaje_match')).'%',
            ],
        ]);
    }

    public function show(Request $request, Postulacion $postulacion): View
    {
        abort_unless($postulacion->perfil_postulante_id === $request->user()->perfil()->id, 403);

        $postulacion->load([
            'vacante' => fn ($q) => $q->withTrashed()->with('empresa'),
            'historial',
            'entrevistas' => fn ($q) => $q->orderBy('fecha_hora'),
        ]);

        return view('postulante.postulaciones.show', compact('postulacion'));
    }

    /** Postular a una vacante. El % de match se calcula y queda guardado. */
    public function store(Request $request, Vacante $vacante, MatchingService $matching, Notificador $notificador): RedirectResponse
    {
        $perfil = $request->user()->perfil();

        $request->validate([
            'carta_presentacion' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless(
            $vacante->estado === 'publicada' && $vacante->empresa->estaVerificada(),
            422,
            'Esta vacante ya no recibe postulaciones.'
        );

        if ($perfil->postulaciones()->where('vacante_id', $vacante->id)->exists()) {
            return back()->with('error', 'Ya postulaste a esta vacante.');
        }

        $postulacion = $perfil->postulaciones()->create([
            'vacante_id' => $vacante->id,
            'carta_presentacion' => $request->carta_presentacion,
            'porcentaje_match' => $matching->calcular($perfil, $vacante)['porcentaje'],
            // estado = 'postulado' por defecto; el trigger trg_historial_postulacion lo registra
        ]);

        // Aviso al reclutador que publicó la vacante (campanita)
        $notificador->enviar($vacante->reclutador, new NuevaPostulacion($postulacion));

        return redirect()->route('postulante.postulaciones.index')
            ->with('exito', "Postulaste a \"{$vacante->titulo}\". Te avisaremos cuando la empresa revise tu perfil.");
    }
}
