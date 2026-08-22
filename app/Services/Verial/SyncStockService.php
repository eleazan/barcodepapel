<?php

declare(strict_types=1);

namespace App\Services\Verial;

use App\Models\Product;
use App\Models\VerialSyncLog;
use Illuminate\Support\Facades\Log;

class SyncStockService
{
    public function __construct(
        private readonly VerialClient $client,
    ) {}

    public function sync(): SyncResult
    {
        $processed     = 0;
        $updated       = 0;
        $errors        = 0;
        $errorMessages = [];

        try {
            $response = $this->client->get('GetStockArticulosWS');
            $stocks   = $response['Stocks'] ?? $response['stocks'] ?? $response;

            if (! is_array($stocks)) {
                $stocks = [];
            }

            foreach ($stocks as $item) {
                $processed++;

                try {
                    $verialId = (int) ($item['CodigoArticulo'] ?? 0);
                    $stock    = (int) ($item['Stock'] ?? $item['Unidades'] ?? 0);

                    if ($verialId === 0) {
                        continue;
                    }

                    $rows = Product::where('verial_id', $verialId)
                        ->update(['stock' => $stock]);

                    if ($rows > 0) {
                        $updated++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    $errorMessages[] = $e->getMessage();
                    Log::warning('Error al actualizar stock Verial', [
                        'item'  => $item,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            VerialSyncLog::record(
                entityType: 'producto',
                entityId: null,
                operation: 'sync_stock',
                verialMethod: 'GetStockArticulosWS',
                response: ['processed' => $processed, 'updated' => $updated, 'errors' => $errors],
            );
        } catch (\Throwable $e) {
            $errors++;
            $errorMessages[] = $e->getMessage();

            VerialSyncLog::record(
                entityType: 'producto',
                entityId: null,
                operation: 'sync_stock',
                verialMethod: 'GetStockArticulosWS',
                response: [],
                error: $e->getMessage(),
            );
        }

        return new SyncResult($processed, 0, $updated, $errors, $errorMessages);
    }
}
