<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Notificaciones dentro de la app (tabla "notifications" de Laravel).
 */
class NotificacionController extends Controller
{
    public function index(Request $request): View
    {
        return view('notificaciones', [
            'notificaciones' => $request->user()->notifications()->paginate(20),
        ]);
    }

    /**
     * Resumen en JSON para la campanita: la consulta cada 30 segundos
     * para mostrar notificaciones nuevas sin recargar la página.
     */
    public function resumen(Request $request): JsonResponse
    {
        return response()->json(self::datosCampanita($request->user()));
    }

    /** Datos que usa la campanita (también al dibujar la página por primera vez). */
    public static function datosCampanita(User $usuario): array
    {
        return [
            'sin_leer' => $usuario->unreadNotifications()->count(),
            'items' => $usuario->notifications()->limit(5)->get()->map(fn ($n) => [
                'id' => $n->id,
                'titulo' => $n->data['titulo'] ?? 'Notificación',
                'mensaje' => $n->data['mensaje'] ?? '',
                'hace' => $n->created_at->diffForHumans(),
                'leida' => $n->read_at !== null,
                'url' => route('notificaciones.abrir', $n->id),
            ])->all(),
        ];
    }

    /** Marca la notificación como leída y lleva a la pantalla relacionada. */
    public function abrir(Request $request, string $id): RedirectResponse
    {
        $notificacion = $request->user()->notifications()->findOrFail($id);
        $notificacion->markAsRead();

        return redirect($notificacion->data['url'] ?? route('dashboard'));
    }

    public function leerTodas(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('exito', 'Todas las notificaciones quedaron como leídas.');
    }
}
