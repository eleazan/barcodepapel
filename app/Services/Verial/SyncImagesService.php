<?php

declare(strict_types=1);

namespace App\Services\Verial;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\VerialSyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SyncImagesService
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
            $response = $this->client->get('GetImagenesArticulosWS');
            $imagenes = $response['Imagenes'] ?? $response['imagenes'] ?? $response;

            if (! is_array($imagenes)) {
                $imagenes = [];
            }

            foreach ($imagenes as $item) {
                $processed++;

                try {
                    $verialId = (int) ($item['CodigoArticulo'] ?? 0);
                    $url      = (string) ($item['URL'] ?? $item['Url'] ?? '');

                    if ($verialId === 0 || $url === '') {
                        continue;
                    }

                    $product = Product::where('verial_id', $verialId)->first();
                    if ($product === null) {
                        continue;
                    }

                    // Descargar imagen
                    $imageResponse = Http::timeout(30)->get($url);
                    if (! $imageResponse->successful()) {
                        continue;
                    }

                    $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    $filename  = 'products/'.Str::uuid().'.'.$extension;

                    Storage::disk('public')->put($filename, $imageResponse->body());

                    // Solo añadir si el producto no tiene aún ninguna imagen con ese path
                    $exists = ProductImage::where('product_id', $product->id)
                        ->where('path', $filename)
                        ->exists();

                    if (! $exists) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path'       => $filename,
                            'sort_order' => 0,
                        ]);
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    $errorMessages[] = $e->getMessage();
                    Log::warning('Error al sincronizar imagen Verial', [
                        'item'  => $item,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            VerialSyncLog::record(
                entityType: 'imagen',
                entityId: null,
                operation: 'sync_images',
                verialMethod: 'GetImagenesArticulosWS',
                response: ['processed' => $processed, 'created' => $created, 'updated' => $updated, 'errors' => $errors],
            );
        } catch (\Throwable $e) {
            $errors++;
            $errorMessages[] = $e->getMessage();

            VerialSyncLog::record(
                entityType: 'imagen',
                entityId: null,
                operation: 'sync_images',
                verialMethod: 'GetImagenesArticulosWS',
                response: [],
                error: $e->getMessage(),
            );
        }

        return new SyncResult($processed, $created, $updated, $errors, $errorMessages);
    }
}
