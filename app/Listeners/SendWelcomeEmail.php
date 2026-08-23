<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Bienvenida al cliente en cuanto confirma su correo.
 *
 * Un fallo de correo no puede tumbar la verificación: solo se registra.
 */
class SendWelcomeEmail
{
    public function handle(Verified $event): void
    {
        try {
            $event->user->notify(new WelcomeNotification);
        } catch (Throwable $e) {
            Log::warning('No se pudo enviar la bienvenida', [
                'user_id' => $event->user->getKey(),
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
