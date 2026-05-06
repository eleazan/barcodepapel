<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\RegisterClientInVerial;
use App\Services\Notifications\Channels\EmailChannel;
use App\Services\Notifications\OrderNotificationService;
use App\Services\Verial\VerialClient;
use Illuminate\Auth\Events\Registered;
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
            $service = new OrderNotificationService();
            $service->registerChannel(new EmailChannel());
            // Register future channels here:
            // $service->registerChannel(new WhatsAppChannel());
            // $service->registerChannel(new TelegramChannel());

            return $service;
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
