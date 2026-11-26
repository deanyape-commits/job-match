<?php

namespace App\Notifications;

use App\Models\Postulacion;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Al postulante: la empresa cambió el estado de su postulación. */
class EstadoPostulacionCambiado extends Notification
{
    private const MENSAJES = [
        'postulado' => 'Tu postulación volvió a la etapa inicial.',
        'en_revision' => 'La empresa está revisando tu perfil.',
        'entrevista' => 'Fuiste seleccionado para la etapa de entrevista.',
        'rechazado' => 'La empresa decidió continuar con otros candidatos. ¡Sigue intentándolo!',
        'contratado' => '¡Felicitaciones! La empresa te seleccionó para el puesto.',
    ];

    public function __construct(public Postulacion $postulacion) {}

    /** Canales: correo (SMTP) y base de datos (campanita). */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vacante = $this->postulacion->vacante;

        return (new MailMessage)
            ->subject("Novedades sobre tu postulación a {$vacante->titulo}")
            ->greeting("Hola, {$notifiable->nombre}")
            ->line("Tu postulación a **{$vacante->titulo}** en {$vacante->empresa->razon_social} cambió de estado.")
            ->line(self::MENSAJES[$this->postulacion->estado] ?? '')
            ->action('Ver mi postulación', route('postulante.postulaciones.show', $this->postulacion))
            ->salutation('El equipo de Ludan');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => $this->postulacion->vacante->titulo,
            'mensaje' => self::MENSAJES[$this->postulacion->estado] ?? 'Tu postulación cambió de estado.',
            'url' => route('postulante.postulaciones.show', $this->postulacion),
            'tipo' => 'estado',
        ];
    }
}
