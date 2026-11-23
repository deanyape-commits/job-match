<?php

namespace App\Services;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as Notificaciones;
use Throwable;

/**
 * Envía notificaciones sin que un problema con el servidor de correo
 * interrumpa la acción del usuario: si el correo falla, se registra en el log
 * y la notificación se guarda igual en la base de datos (campanita).
 */
class Notificador
{
    public function enviar(mixed $destinatarios, Notification $notificacion): void
    {
        try {
            Notificaciones::send($destinatarios, $notificacion);
        } catch (Throwable $e) {
            Log::warning('No se pudo enviar el correo de '.class_basename($notificacion).': '.$e->getMessage());
            Notificaciones::sendNow($destinatarios, $notificacion, ['database']);
        }
    }
}
