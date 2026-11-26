<?php

namespace App\Notifications;

use App\Models\Entrevista;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Al postulante: le agendaron una entrevista virtual (con el enlace de Jitsi). */
class EntrevistaAgendada extends Notification
{
    public function __construct(public Entrevista $entrevista) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vacante = $this->entrevista->postulacion->vacante;
        $fecha = ucfirst($this->entrevista->fecha_hora->translatedFormat('l j \d\e F \a \l\a\s g:i a'));

        return (new MailMessage)
            ->subject("Entrevista agendada: {$vacante->titulo}")
            ->greeting("Hola, {$notifiable->nombre}")
            ->line("{$vacante->empresa->razon_social} te invita a una entrevista virtual para el puesto de **{$vacante->titulo}**.")
            ->line("**Fecha:** {$fecha}")
            ->line("**Duración:** {$this->entrevista->duracion_minutos} minutos")
            ->line('La entrevista es por videollamada en Jitsi Meet: no necesitas instalar nada ni crear una cuenta.')
            ->action('Unirme a la videollamada', $this->entrevista->enlace_reunion)
            ->salutation('¡Mucho éxito! El equipo de Ludan');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'titulo' => 'Entrevista: '.$this->entrevista->postulacion->vacante->titulo,
            'mensaje' => 'Agendada para el '.$this->entrevista->fecha_hora->translatedFormat('j \d\e F, g:i a').'.',
            'url' => route('postulante.postulaciones.show', $this->entrevista->postulacion),
            'tipo' => 'entrevista',
        ];
    }
}
