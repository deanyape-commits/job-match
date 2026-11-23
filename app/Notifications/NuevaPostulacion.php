<?php

namespace App\Notifications;

use App\Models\Postulacion;
use Illuminate\Notifications\Notification;

/**
 * Al reclutador: alguien postuló a su vacante.
 * Solo en la campanita (base de datos), para no llenar su correo.
 */
class NuevaPostulacion extends Notification
{
    public function __construct(public Postulacion $postulacion) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $nombre = $this->postulacion->perfilPostulante->usuario->nombre;

        return [
            'titulo' => 'Nuevo candidato: '.$this->postulacion->vacante->titulo,
            'mensaje' => "{$nombre} postuló con ".round($this->postulacion->porcentaje_match).'% de compatibilidad.',
            'url' => route('reclutador.postulaciones.show', $this->postulacion),
            'tipo' => 'postulacion',
        ];
    }
}
