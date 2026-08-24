<?php

declare(strict_types=1);

namespace App\Jobs\Verial;

use App\Models\Order;
use App\Services\Verial\SendOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderToVerialJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 120;

    public function __construct(
        private readonly Order $order,
    ) {}

    public function handle(SendOrderService $service): void
    {
        $service->send($this->order);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendOrderToVerialJob falló definitivamente', [
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'error'        => $exception->getMessage(),
        ]);
    }
}
