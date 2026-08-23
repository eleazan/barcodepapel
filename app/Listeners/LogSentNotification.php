<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Email;
use Throwable;

/**
 * Deja constancia de los avisos de cuenta en `notification_logs`, el mismo
 * sitio donde ya se registran los correos de pedido.
 *
 * Así el historial de un cliente está completo: verificación, contraseña y
 * bienvenida se ven junto a los avisos de sus pedidos. Los correos de pedido no
 * pasan por aquí —los envía EmailChannel y se registran él mismo—, así que no
 * hay duplicados.
 */
class LogSentNotification
{
    /** Notificaciones propias y su evento en el registro. */
    private const EVENTOS = [
        VerifyEmailNotification::class   => NotificationLog::EVENT_EMAIL_VERIFICATION,
        ResetPasswordNotification::class => NotificationLog::EVENT_PASSWORD_RESET,
        WelcomeNotification::class       => NotificationLog::EVENT_WELCOME,
    ];

    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'mail' || ! $event->notifiable instanceof User) {
            return;
        }

        try {
            NotificationLog::create([
                'user_id'   => $event->notifiable->getKey(),
                'channel'   => NotificationLog::CHANNEL_EMAIL,
                'recipient' => (string) $event->notifiable->email,
                'subject'   => $this->asunto($event),
                'body'      => $this->cuerpo($event),
                'status'    => NotificationLog::STATUS_SENT,
                'event'     => self::EVENTOS[$event->notification::class] ?? NotificationLog::EVENT_OTHER,
                'sent_at'   => now(),
                'metadata'  => ['notification' => $event->notification::class],
            ]);
        } catch (Throwable $e) {
            // Registrar el aviso nunca puede tumbar el envío en sí.
            Log::warning('No se pudo registrar la notificación enviada', [
                'notifiable' => $event->notifiable->getKey(),
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function asunto(NotificationSent $event): ?string
    {
        return $this->correo($event)?->getSubject();
    }

    /**
     * Se guarda la versión en texto para poder leer el historial sin renderizar
     * nada; si el correo solo lleva HTML, se deja constancia del tipo de aviso.
     */
    private function cuerpo(NotificationSent $event): string
    {
        $correo = $this->correo($event);
        $texto  = $correo?->getTextBody();

        if (is_string($texto) && trim($texto) !== '') {
            return $texto;
        }

        return $this->asunto($event) ?? 'Aviso enviado al cliente.';
    }

    private function correo(NotificationSent $event): ?Email
    {
        $mensaje = $event->response?->getSymfonySentMessage()?->getOriginalMessage();

        return $mensaje instanceof Email ? $mensaje : null;
    }
}
