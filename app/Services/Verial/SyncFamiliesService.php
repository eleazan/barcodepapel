<?php

declare(strict_types=1);

namespace App\Services\Verial;

use App\Models\Category;
use App\Models\VerialSyncLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncFamiliesService
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
            $response = $this->client->get('GetFamiliaArticulosWS');
            $familias = $response['Familias'] ?? $response['familias'] ?? $response;

            if (! is_array($familias)) {
                $familias = [];
            }

            foreach ($familias as $item) {
                $processed++;

                try {
                    $verialFamiliaId = (int) ($item['CodigoFamilia'] ?? $item['Codigo'] ?? 0);
                    $nombre          = (string) ($item['Nombre'] ?? $item['nombre'] ?? '');

                    if ($verialFamiliaId === 0 || $nombre === '') {
                        continue;
                    }

                    $exists = Category::where('verial_familia_id', $verialFamiliaId)->exists();

                    Category::updateOrCreate(
                        ['verial_familia_id' => $verialFamiliaId],
                        [
                            'name'      => $nombre,
                            'slug'      => Str::slug($nombre),
                            'is_active' => true,
                        ]
                    );

                    $exists ? $updated++ : $created++;
                } catch (\Throwable $e) {
                    $errors++;
                    $errorMessages[] = $e->getMessage();
                    Log::warning('Error al sincronizar familia Verial', [
                        'item'  => $item,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            VerialSyncLog::record(
                entityType: 'categoria',
                entityId: null,
                operation: 'sync',
                verialMethod: 'GetFamiliaArticulosWS',
                response: ['processed' => $processed, 'created' => $created, 'updated' => $updated, 'errors' => $errors],
            );
        } catch (\Throwable $e) {
            $errors++;
            $errorMessages[] = $e->getMessage();

            VerialSyncLog::record(
                entityType: 'categoria',
                entityId: null,
                operation: 'sync',
                verialMethod: 'GetFamiliaArticulosWS',
                response: [],
                error: $e->getMessage(),
            );
        }

        return new SyncResult($processed, $created, $updated, $errors, $errorMessages);
    }
}
