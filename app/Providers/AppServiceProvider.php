<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\LogSentNotification;
use App\Listeners\RegisterClientInVerial;
use App\Listeners\SendWelcomeEmail;
use App\Services\Cart\Cart;
use App\Services\Delivery\DeliveryCalendar;
use App\Services\Notifications\Channels\EmailChannel;
use App\Services\Notifications\OrderNotificationService;
use App\Services\Verial\VerialClient;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OrderNotificationService::class, function () {
            $service = new OrderNotificationService;
            $service->registerChannel(new EmailChannel);
            // Register future channels here:
            // $service->registerChannel(new WhatsAppChannel());
            // $service->registerChannel(new TelegramChannel());

            return $service;
        });

        // El carrito vive en la sesión: una sola instancia por petición para que
        // controladores, vistas y componentes vean el mismo estado reconciliado.
        $this->app->scoped(Cart::class);

        // Los festivos y cierres se leen una sola vez por petición.
        $this->app->scoped(DeliveryCalendar::class);

        $this->app->singleton(VerialClient::class, function () {
            $cfg = config('verial');

            return new VerialClient(
                host: $cfg['host'],
                port: $cfg['port'],
                session: $cfg['session'],
                timeout: $cfg['timeout'],
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registro de cliente Verial al registrarse un usuario
        Event::listen(Registered::class, RegisterClientInVerial::class);

        // Bienvenida en cuanto el cliente confirma su correo
        Event::listen(Verified::class, SendWelcomeEmail::class);

        // Los avisos de cuenta quedan en el mismo historial que los de pedido
        Event::listen(NotificationSent::class, LogSentNotification::class);

        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Set default password validation rules
        Password::defaults(function () {
            $rule = Password::min(8);

            return $this->app->isProduction()
                ? $rule->mixedCase()->uncompromised()
                : $rule;
        });
    }
}
