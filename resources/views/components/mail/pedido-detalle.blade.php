@props([
    'order',
])

{{-- Líneas del pedido, totales y dirección: se reutiliza en el correo al
     cliente y en la copia que le llega a la librería. --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 20px; border-collapse:collapse;">
    <tr>
        <th align="left" style="padding:8px 0; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:#9ca3af; border-bottom:1px solid #eef2f5;">Producto</th>
        <th align="right" style="padding:8px 0; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:#9ca3af; border-bottom:1px solid #eef2f5;">Importe</th>
    </tr>
    @foreach ($order->items as $item)
        <tr>
            <td style="padding:10px 0; font-size:14px; color:#374151; border-bottom:1px solid #f6f8fa;">
                <strong>{{ $item->quantity }}&times;</strong> {{ $item->product?->name ?? 'Producto' }}
            </td>
            <td align="right" style="padding:10px 0; font-size:14px; color:#111827; border-bottom:1px solid #f6f8fa; white-space:nowrap;">
                {{ number_format((float) $item->total, 2, ',', '.') }} &euro;
            </td>
        </tr>
    @endforeach
    <tr>
        <td style="padding:10px 0 2px; font-size:14px; color:#6b7280;">Subtotal</td>
        <td align="right" style="padding:10px 0 2px; font-size:14px; color:#374151;">{{ $order->formattedSubtotal() }}</td>
    </tr>
    <tr>
        <td style="padding:2px 0; font-size:14px; color:#6b7280;">Reparto</td>
        <td align="right" style="padding:2px 0; font-size:14px; color:#374151;">{{ $order->formattedDeliveryFee() }}</td>
    </tr>
    <tr>
        <td style="padding:10px 0 0; font-size:16px; font-weight:600; color:#111827; border-top:2px solid #00b5b5;">Total</td>
        <td align="right" style="padding:10px 0 0; font-size:16px; font-weight:600; color:#0e7490; border-top:2px solid #00b5b5;">{{ $order->formattedTotal() }}</td>
    </tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0fdfa; border-radius:12px;">
    <tr>
        <td style="padding:16px 18px; font-size:14px; line-height:1.6; color:#374151;">
            <strong style="color:#111827;">Entrega</strong><br>
            {{ $order->delivery_address }}<br>
            CP {{ $order->postal_code }}
            @if ($order->formattedEstimatedDelivery())
                <br><span style="color:#6b7280;">Fecha prevista:</span>
                <strong style="text-transform:lowercase;">{{ $order->formattedEstimatedDelivery() }}</strong>
            @endif
            @if ($order->notes)
                <br><span style="color:#6b7280;">Indicaciones:</span> {{ $order->notes }}
            @endif
        </td>
    </tr>
</table>
