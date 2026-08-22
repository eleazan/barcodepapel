<?php

declare(strict_types=1);

namespace App\Services\Verial;

use App\Models\User;
use App\Models\VerialSyncLog;
use Illuminate\Support\Facades\Log;

class RegisterClientService
{
    public function __construct(
        private readonly VerialClient $client,
    ) {}

    /**
     * Registra un usuario como cliente en Verial.
     * Si el usuario ya tiene verial_cliente_id asignado, no hace nada.
     */
    public function register(User $user): void
    {
        if ($user->hasVerialCliente()) {
            return;
        }

        try {
            $response = $this->client->post('NuevoClienteWS', [
                'Nombre' => $user->name,
                'Email'  => $user->email,
            ]);

            $clienteId = (int) ($response['CodigoCliente'] ?? $response['Codigo'] ?? 0);

            if ($clienteId > 0) {
                $user->update(['verial_cliente_id' => $clienteId]);
            }

            VerialSyncLog::record(
                entityType: 'cliente',
                entityId: $user->id,
                operation: 'register_client',
                verialMethod: 'NuevoClienteWS',
                response: $response,
            );
        } catch (\Throwable $e) {
            Log::warning('Error al registrar cliente en Verial', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            VerialSyncLog::record(
                entityType: 'cliente',
                entityId: $user->id,
                operation: 'register_client',
                verialMethod: 'NuevoClienteWS',
                response: [],
                error: $e->getMessage(),
            );
        }
    }
}
