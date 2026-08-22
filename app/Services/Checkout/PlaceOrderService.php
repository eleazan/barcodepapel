<?php

declare(strict_types=1);

namespace App\Services\Checkout;

use App\Exceptions\CheckoutException;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Product;
use App\Services\Cart\Cart;
use App\Services\Delivery\DeliveryZoneResolver;
use App\Services\Notifications\OrderNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Convierte el carrito de sesión en un pedido confirmado.
 *
 * El stock se descuenta dentro de la transacción con bloqueo de fila, para que
 * dos clientes simultáneos no puedan comprar la última unidad.
 */
class PlaceOrderService
{
    public function __construct(
        private readonly Cart $cart,
        private readonly DeliveryZoneResolver $zones,
        private readonly OrderNotificationService $notifications,
    ) {}

    /**
     * @param  array{customer_name: string, customer_email: ?string, customer_phone: string, delivery_address: string, postal_code: string, notes: ?string}  $data
     *
     * @throws CheckoutException
     */
    public function place(array $data, ?int $userId = null): Order
    {
        if ($this->cart->isEmpty()) {
            throw CheckoutException::carritoVacio();
        }

        $zone = $this->zones->resolve($data['postal_code']);

        if ($zone === null) {
            throw CheckoutException::fueraDeZona($data['postal_code']);
        }

        // Fotografía del carrito antes de abrir la transacción.
        $lines = $this->cart->items()
            ->map(fn ($item) => ['product_id' => $item->product->id, 'quantity' => $item->quantity])
            ->all();

        $order = DB::transaction(function () use ($lines, $data, $zone, $userId): Order {
            $subtotal   = 0.0;
            $orderItems = [];

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($line['product_id']);

                if (! $product->is_active || $product->stock < $line['quantity']) {
                    throw CheckoutException::sinStock($product->name, (int) $product->stock);
                }

                $lineTotal = round((float) $product->price * $line['quantity'], 2);
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity'   => $line['quantity'],
                    'unit_price' => $product->price,
                    'total'      => $lineTotal,
                    'verial_id'  => $product->verial_id,
                ];

                $product->decrement('stock', $line['quantity']);
            }

            $deliveryFee = (float) $zone->delivery_fee;

            // La fecha prevista sale de los días de reparto de la zona: el día
            // que se le anuncia al cliente queda registrado con el pedido.
            $order = Order::create([
                'user_id'                 => $userId,
                'customer_name'           => $data['customer_name'],
                'customer_email'          => $data['customer_email'] ?? null,
                'customer_phone'          => $data['customer_phone'],
                'delivery_address'        => $data['delivery_address'],
                'postal_code'             => $zone->postal_code,
                'status'                  => Order::STATUS_PENDIENTE,
                'subtotal'                => round($subtotal, 2),
                'delivery_fee'            => $deliveryFee,
                'total'                   => round($subtotal + $deliveryFee, 2),
                'estimated_delivery_date' => $zone->nextDeliveryDate(),
                'notes'                   => $data['notes'] ?? null,
            ]);

            $order->items()->createMany($orderItems);

            return $order;
        });

        $this->cart->clear();

        $order->load('items.product');

        $this->notifyCustomer($order);

        // El envío a Verial no ocurre aquí: el pedido nace "pendiente" y se
        // manda al ERP cuando el admin lo marca como "preparado" (ver
        // Admin\OrderController::updateStatus y verial:send-pending-orders).
        return $order;
    }

    /**
     * Acuse de recibo al cliente. Un fallo de correo no invalida el pedido.
     */
    private function notifyCustomer(Order $order): void
    {
        try {
            $this->notifications->sendAll($order, NotificationLog::EVENT_ORDER_CREATED);
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar la confirmación del pedido', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
