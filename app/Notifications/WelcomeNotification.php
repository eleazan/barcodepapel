<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Bienvenida al cliente que acaba de confirmar su correo.
 *
 * Se manda al verificar y no al registrarse, para no soltarle dos correos a la
 * vez y porque hasta entonces la cuenta no está operativa.
 */
class WelcomeNotification extends Notification
{
    use Queueable;

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Te damos la bienvenida a '.config('tienda.nombre'))
            ->view('emails.auth.welcome', ['nombre' => $notifiable->name])
            ->replyTo(config('tienda.email'), config('tienda.nombre'));
    }
}
