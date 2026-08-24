<?php

declare(strict_types=1);

namespace App\Services\Verial;

use App\Models\VerialFabricante;
use App\Models\VerialSyncLog;
use Illuminate\Support\Facades\Log;

class SyncFabricantesService
{
    public function __construct(
        private readonly VerialClient $client,
    ) {}

    public function sync(): SyncResult
    {
        $processed     = 0;
        $created       = 0;
        $updated       = 0;
        $errors        = 0;
        $errorMessages = [];

        try {
            $response    = $this->client->get('GetFabricantesWS');
            $fabricantes = $response['Fabricantes'] ?? $response['fabricantes'] ?? $response;

            if (! is_array($fabricantes)) {
                $fabricantes = [];
            }

            foreach ($fabricantes as $item) {
                $processed++;

                try {
                    $verialId = (int) ($item['CodigoFabricante'] ?? $item['Codigo'] ?? 0);
                    $nombre   = (string) ($item['Nombre'] ?? $item['nombre'] ?? '');

                    if ($verialId === 0) {
                        continue;
                    }

                    $exists = VerialFabricante::where('verial_id', $verialId)->exists();

                    VerialFabricante::updateOrCreate(
                        ['verial_id' => $verialId],
                        ['nombre' => $nombre]
                    );

                    $exists ? $updated++ : $created++;
                } catch (\Throwable $e) {
                    $errors++;
                    $errorMessages[] = $e->getMessage();
                    Log::warning('Error al sincronizar fabricante Verial', [
                        'item'  => $item,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            VerialSyncLog::record(
                entityType: 'fabricante',
                entityId: null,
                operation: 'sync',
                verialMethod: 'GetFabricantesWS',
                response: ['processed' => $processed, 'created' => $created, 'updated' => $updated, 'errors' => $errors],
            );
        } catch (\Throwable $e) {
            $errors++;
            $errorMessages[] = $e->getMessage();

            VerialSyncLog::record(
                entityType: 'fabricante',
                entityId: null,
                operation: 'sync',
                verialMethod: 'GetFabricantesWS',
                response: [],
                error: $e->getMessage(),
            );
        }

        return new SyncResult($processed, $created, $updated, $errors, $errorMessages);
    }
}
