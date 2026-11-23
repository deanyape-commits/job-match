<?php

namespace App\Http\Controllers\Reclutador;

use App\Http\Controllers\Controller;
use App\Models\Entrevista;
use App\Models\Postulacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Entrevistas virtuales. El enlace de videollamada se genera con Jitsi Meet
 * (https://meet.jit.si/...), que no requiere cuenta ni API.
 */
class EntrevistaController extends Controller
{
    public function index(Request $request): View
    {
        $base = Entrevista::with(['postulacion.perfilPostulante.usuario', 'postulacion.vacante'])
            ->whereHas('postulacion.vacante', fn ($q) => $q->where('empresa_id', $request->user()->empresa_id));

        return view('reclutador.entrevistas.index', [
            'proximas' => (clone $base)->where('estado', 'programada')->where('fecha_hora', '>=', now()->subHour())->orderBy('fecha_hora')->get(),
            'anteriores' => (clone $base)->where(fn ($q) => $q->where('estado', '!=', 'programada')->orWhere('fecha_hora', '<', now()->subHour()))
                ->orderByDesc('fecha_hora')->limit(20)->get(),
        ]);
    }

    /** Agendar entrevista para una postulación. */
    public function store(Request $request, Postulacion $postulacion): RedirectResponse
    {
        Gate::authorize('gestionar', $postulacion);

        $datos = $request->validateWithBag('entrevista', [
            'fecha_hora' => ['required', 'date', 'after:now'],
            'duracion_minutos' => ['required', 'integer', 'in:15,30,45,60,90'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ], [
            'fecha_hora.after' => 'La entrevista debe ser en una fecha y hora futuras.',
        ]);

        DB::transaction(function () use ($postulacion, $datos) {
            $postulacion->entrevistas()->create($datos + [
                'enlace_reunion' => Entrevista::generarEnlaceJitsi(),
                'estado' => 'programada',
            ]);

            // Agendar una entrevista mueve la postulación a la etapa "entrevista"
            if (in_array($postulacion->estado, ['postulado', 'en_revision'], true)) {
                $postulacion->update(['estado' => 'entrevista']);
            }
        });

        return redirect()->route('reclutador.postulaciones.show', $postulacion)
            ->with('exito', 'Entrevista agendada. El enlace de videollamada ya está disponible para el postulante.');
    }

    /** Marcar como realizada o cancelada. */
    public function update(Request $request, Entrevista $entrevista): RedirectResponse
    {
        Gate::authorize('gestionar', $entrevista->postulacion);

        $request->validate([
            'estado' => ['required', 'in:programada,realizada,cancelada'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ]);

        $entrevista->update($request->only('estado', 'notas'));

        return back()->with('exito', 'Entrevista actualizada.');
    }
}
