<?php

declare(strict_types=1);

namespace App\Jobs\Verial;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        private readonly array $stocks,
    ) {}

    public function handle(): void
    {
        $updated = 0;

        foreach ($this->stocks as $item) {
            $verialId = (int) ($item['CodigoArticulo'] ?? 0);
            $stock    = (int) ($item['Stock'] ?? $item['Unidades'] ?? 0);

            if ($verialId === 0) {
                continue;
            }

            $rows = Product::where('verial_id', $verialId)->update(['stock' => $stock]);
            if ($rows > 0) {
                $updated++;
            }
        }

        Log::info('UpdateStockJob: stock actualizado', ['actualizados' => $updated]);
    }
}
