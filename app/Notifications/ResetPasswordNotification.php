<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Recuperación de contraseña con la maquetación de la tienda.
 */
class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Recupera tu contraseña · '.config('tienda.nombre'))
            ->view('emails.auth.reset-password', [
                'nombre'  => $notifiable->name,
                'url'     => $this->resetUrl($notifiable),
                'minutos' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ])
            ->replyTo(config('tienda.email'), config('tienda.nombre'));
    }
}
