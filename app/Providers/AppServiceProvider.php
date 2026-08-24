<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\LogSentNotification;
use App\Listeners\RegisterClientInVerial;
use App\Listeners\SendWelcomeEmail;
use App\Services\Cart\Cart;
use App\Services\Delivery\DeliveryCalendar;
use App\Services\Jobs\BatchTaskRegistry;
use App\Services\Jobs\Tasks\BookCoverTask;
use App\Services\Notifications\Channels\EmailChannel;
use App\Services\Notifications\OrderNotificationService;
use App\Services\Verial\VerialClient;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
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

        // Tareas del panel /admin/jobs. Añadir una tarea nueva es registrarla
        // aquí, igual que los canales de notificación.
        $this->app->singleton(BatchTaskRegistry::class, function ($app) {
            $registry = new BatchTaskRegistry;
            $registry->register($app->make(BookCoverTask::class));

            return $registry;
        });

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

        // Tope de peticiones a Google Books (100 por cada 100 s en su API).
        // Red de seguridad del job: el ritmo lo marca el retardo del lote.
        RateLimiter::for('google-books', fn () => Limit::perMinute(
            (int) config('services.google_books.per_minute', 60)
        ));

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
