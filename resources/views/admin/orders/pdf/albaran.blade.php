<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Albarán {{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; line-height: 1.5; }

        .page { padding: 40px; }

        /* Header */
        .header { display: table; width: 100%; margin-bottom: 30px; border-bottom: 2px solid #0ea5e9; padding-bottom: 20px; }
        .header-left { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { display: table-cell; vertical-align: top; width: 50%; text-align: right; }
        .logo { font-size: 22px; font-weight: bold; color: #0369a1; }
        .logo-sub { font-size: 10px; color: #9ca3af; margin-top: 2px; }
        .order-number { font-size: 18px; font-weight: bold; color: #1f2937; }
        .order-date { font-size: 11px; color: #6b7280; margin-top: 4px; }

        /* Status badge */
        .status { display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-pendiente { background: #fef3c7; color: #92400e; }
        .status-preparado { background: #e0f2fe; color: #075985; }
        .status-en_reparto { background: #ede9fe; color: #5b21b6; }
        .status-entregado { background: #d1fae5; color: #065f46; }

        /* Info blocks */
        .info-row { display: table; width: 100%; margin-bottom: 24px; }
        .info-block { display: table-cell; vertical-align: top; width: 50%; }
        .info-block h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #0ea5e9; margin-bottom: 6px; font-weight: bold; }
        .info-block p { font-size: 11px; color: #374151; margin-bottom: 2px; }
        .info-block .label { color: #9ca3af; font-size: 10px; }

        /* Items table */
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        table.items thead th { background: #f0f9ff; color: #0369a1; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; padding: 10px 12px; text-align: left; border-bottom: 1px solid #bae6fd; }
        table.items thead th.right { text-align: right; }
        table.items thead th.center { text-align: center; }
        table.items tbody td { padding: 10px 12px; border-bottom: 1px solid #f0f9ff; font-size: 11px; }
        table.items tbody td.right { text-align: right; }
        table.items tbody td.center { text-align: center; }
        table.items tbody tr:last-child td { border-bottom: none; }

        /* Totals */
        .totals { width: 280px; margin-left: auto; }
        .totals table { width: 100%; }
        .totals td { padding: 4px 0; font-size: 11px; }
        .totals td.label { color: #6b7280; }
        .totals td.value { text-align: right; }
        .totals tr.total td { border-top: 2px solid #0ea5e9; padding-top: 8px; font-size: 14px; font-weight: bold; color: #1f2937; }

        /* Notes */
        .notes { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; }
        .notes h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin-bottom: 4px; }
        .notes p { font-size: 11px; color: #374151; }

        /* Footer */
        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 9px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="page">

        {{-- Header --}}
        <div class="header">
            <div class="header-left">
                <div class="logo">{{ config('tienda.nombre') }}</div>
                <div class="logo-sub">{{ config('tienda.direccion.calle') }} &middot; {{ config('tienda.direccion.codigo_postal') }} {{ config('tienda.direccion.ciudad') }}, {{ config('tienda.direccion.provincia') }}</div>
                <div class="logo-sub">Tel. {{ config('tienda.telefono.display') }}</div>
            </div>
            <div class="header-right">
                <div class="order-number">{{ $order->order_number }}</div>
                <div class="order-date">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                <div style="margin-top: 6px;">
                    <span class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                </div>
            </div>
        </div>

        {{-- Customer & Delivery info --}}
        <div class="info-row">
            <div class="info-block">
                <h3>Cliente</h3>
                <p><strong>{{ $order->customer_name }}</strong></p>
                <p>{{ $order->customer_phone }}</p>
                @if ($order->customer_email)
                    <p>{{ $order->customer_email }}</p>
                @endif
            </div>
            <div class="info-block">
                <h3>Dirección de entrega</h3>
                <p>{{ $order->delivery_address }}</p>
                <p>CP: {{ $order->postal_code }}</p>
            </div>
        </div>

        {{-- Items --}}
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 50%;">Producto</th>
                    <th class="center">Cant.</th>
                    <th class="right">Precio ud.</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product->name }}</strong>
                            @if ($item->product->sku)
                                <br><span style="color: #9ca3af; font-size: 10px;">SKU: {{ $item->product->sku }}</span>
                            @endif
                        </td>
                        <td class="center">{{ $item->quantity }}</td>
                        <td class="right">{{ number_format((float) $item->unit_price, 2, ',', '.') }} &euro;</td>
                        <td class="right"><strong>{{ number_format((float) $item->total, 2, ',', '.') }} &euro;</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals">
            <table>
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="value">{{ number_format((float) $order->subtotal, 2, ',', '.') }} &euro;</td>
                </tr>
                <tr>
                    <td class="label">Envío</td>
                    <td class="value">{{ number_format((float) $order->delivery_fee, 2, ',', '.') }} &euro;</td>
                </tr>
                <tr class="total">
                    <td>Total</td>
                    <td class="value">{{ number_format((float) $order->total, 2, ',', '.') }} &euro;</td>
                </tr>
            </table>
        </div>

        {{-- Notes --}}
        @if ($order->notes)
            <div class="notes">
                <h3>Notas</h3>
                <p>{{ $order->notes }}</p>
            </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            Documento generado el {{ now()->format('d/m/Y H:i') }} &middot; {{ config('tienda.nombre') }} &middot; {{ config('tienda.direccion.ciudad') }}, Ibiza
        </div>

    </div>
</body>
</html>
