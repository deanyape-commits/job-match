<?php

namespace App\Http\Controllers;

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
