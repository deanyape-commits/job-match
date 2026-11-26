<?php

namespace App\Notifications;

use App\Models\Empresa;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** A los reclutadores: el supervisor verificó o rechazó su empresa. */
class EmpresaRevisada extends Notification
{
    public function __construct(public Empresa $empresa) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verificada = $this->empresa->estaVerificada();

        $correo = (new MailMessage)
            ->subject($verificada ? 'Tu empresa fue verificada en Ludan' : 'Tu empresa no fue aprobada en Ludan')
            ->greeting("Hola, {$notifiable->nombre}");

        return $verificada
            ? $correo->line("¡Buenas noticias! Verificamos los datos de **{$this->empresa->razon_social}**.")
                ->line('Desde ahora puedes publicar vacantes y recibir candidatos.')
                ->action('Publicar mi primera vacante', route('reclutador.vacantes.create'))
                ->salutation('El equipo de Ludan')
            : $correo->line("No pudimos verificar los datos de **{$this->empresa->razon_social}** (RUC {$this->empresa->ruc}).")
                ->line('Si crees que se trata de un error, responde a este correo con la documentación de tu empresa.')
                ->salutation('El equipo de Ludan');
    }

    public function toArray(object $notifiable): array
    {
        $verificada = $this->empresa->estaVerificada();

        return [
            'titulo' => $verificada ? 'Empresa verificada' : 'Empresa rechazada',
            'mensaje' => $verificada
                ? "{$this->empresa->razon_social} ya puede publicar vacantes."
                : "No pudimos verificar los datos de {$this->empresa->razon_social}.",
            'url' => route('reclutador.inicio'),
            'tipo' => $verificada ? 'verificada' : 'rechazada',
        ];
    }
}
