<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\Verial\RegisterClientService;
use App\Services\Verial\VerialClient;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class RegisterClientInVerial
{
    public function __construct(
        private readonly VerialClient $client,
        private readonly RegisterClientService $registerService,
    ) {}

    public function handle(Registered $event): void
    {
        if (! $this->client->isConfigured()) {
            return;
        }

        try {
            $this->registerService->register($event->user);
        } catch (\Throwable $e) {
            // No bloquear el registro del usuario si Verial falla
            Log::warning('No se pudo registrar el cliente en Verial tras el registro', [
                'user_id' => $event->user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
