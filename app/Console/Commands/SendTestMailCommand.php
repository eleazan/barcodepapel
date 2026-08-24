<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envía un correo de prueba para comprobar la configuración SMTP sin tener
 * que hacer un pedido real.
 */
class SendTestMailCommand extends Command
{
    protected $signature = 'mail:test {destinatario : Dirección a la que enviar la prueba}';

    protected $description = 'Envía un correo de prueba para comprobar la configuración de correo';

    public function handle(): int
    {
        $destinatario = (string) $this->argument('destinatario');

        if (! filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            $this->error("«{$destinatario}» no es una dirección de correo válida.");

            return self::FAILURE;
        }

        $this->line('Enviando con esta configuración:');
        $this->table(['Ajuste', 'Valor'], [
            ['Transporte', (string) config('mail.default')],
            ['Servidor', (string) config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port')],
            ['Usuario', (string) (config('mail.mailers.smtp.username') ?: '—')],
            ['Remitente', config('mail.from.address').' ('.config('mail.from.name').')'],
            ['Responder a', (string) config('tienda.email')],
        ]);

        if (config('mail.default') === 'log') {
            $this->warn('MAIL_MAILER=log: el correo se escribirá en storage/logs, no se enviará.');
        }

        try {
            Mail::raw($this->cuerpo(), function ($message) use ($destinatario) {
                $message->to($destinatario)
                    ->subject('Prueba de correo · '.config('tienda.nombre'))
                    ->replyTo(config('tienda.email'), config('tienda.nombre'));
            });
        } catch (Throwable $e) {
            $this->error('No se pudo enviar: '.$e->getMessage());
            $this->newLine();
            $this->line('Con Gmail, los fallos habituales son:');
            $this->line(' · Falta la verificación en dos pasos en la cuenta.');
            $this->line(' · La contraseña de aplicación se pegó con espacios.');
            $this->line(' · MAIL_PORT=587 necesita MAIL_SCHEME=smtp; el 465, MAIL_SCHEME=smtps.');

            return self::FAILURE;
        }

        $this->info("Correo enviado a {$destinatario}.");
        $this->line('Si no llega, revisa la carpeta de spam y la cabecera «Enviado en nombre de».');

        return self::SUCCESS;
    }

    private function cuerpo(): string
    {
        return implode("\n", [
            'Esto es una prueba de la configuración de correo de '.config('tienda.nombre').'.',
            '',
            'Si lo estás leyendo, la tienda puede avisar a los clientes de sus pedidos.',
            '',
            'Enviado el '.now()->translatedFormat('j \d\e F \d\e Y \a \l\a\s H:i').'.',
        ]);
    }
}
