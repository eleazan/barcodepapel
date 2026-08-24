<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Confirmación de correo con la maquetación de la tienda, en lugar de la
 * plantilla genérica de Laravel.
 */
class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirma tu correo · '.config('tienda.nombre'))
            ->view('emails.auth.verify', [
                'nombre' => $notifiable->name,
                'url'    => $this->verificationUrl($notifiable),
            ])
            ->replyTo(config('tienda.email'), config('tienda.nombre'));
    }
}
