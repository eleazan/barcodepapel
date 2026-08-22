<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\OrderItem;

it('deja claro que el albarán lleva el IVA incluido y no es una factura', function () {
    $order = Order::factory()->create();
    OrderItem::factory()->create(['order_id' => $order->id]);

    $html = view('admin.orders.pdf.albaran', ['order' => $order->load('items.product')])->render();

    expect($html)
        ->toContain('IVA incluido en los precios')
        ->toContain('No tiene la consideración de factura')
        ->toContain('nuestro sistema de gestión');
});
