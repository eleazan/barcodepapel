@php
    $entregado = $order->status === \App\Models\Order::STATUS_ENTREGADO;
@endphp

<x-mail.layout
    :titulo="$titulo"
    :preheader="'Pedido ' . $order->order_number . ' · ' . $order->statusLabel()"
    motivo="has hecho un pedido en nuestra tienda"
>
    <p style="margin:0 0 16px;">¡Hola, {{ $order->customer_name }}!</p>

    <p style="margin:0 0 20px;">{{ $mensaje }}</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px; background-color:#f9fafb; border-radius:12px;">
        <tr>
            <td style="padding:16px 18px; font-size:14px; line-height:1.7; color:#374151;">
                <span style="color:#6b7280;">Pedido</span> <strong>{{ $order->order_number }}</strong><br>
                <span style="color:#6b7280;">Estado</span> <strong>{{ $order->statusLabel() }}</strong><br>
                <span style="color:#6b7280;">Total</span> <strong>{{ $order->formattedTotal() }}</strong>
                @if (! $entregado && $order->formattedEstimatedDelivery())
                    <br><span style="color:#6b7280;">Fecha prevista</span> <strong>{{ $order->formattedEstimatedDelivery() }}</strong>
                @endif
            </td>
        </tr>
    </table>

    @if ($entregado)
        <p style="margin:0 0 16px;">
            Esperamos que disfrutes de la compra. Si algo no está como esperabas, tienes 14 días para
            decírnoslo: escríbenos o acércate por la tienda y lo solucionamos.
        </p>

        <x-mail.boton :url="route('catalog')">
            Volver al catálogo
        </x-mail.boton>
    @else
        <p style="margin:0 0 16px;">
            Te recordamos que el pago se realiza en el momento de la entrega, en efectivo o con tarjeta.
        </p>

        <x-mail.boton :url="route('checkout.confirmation', $order->order_number)">
            Ver mi pedido
        </x-mail.boton>
    @endif

    <p style="margin:0; font-size:14px; color:#6b7280;">
        Cualquier duda, responde a este correo o llámanos al {{ config('tienda.telefono.display') }}.
    </p>
</x-mail.layout>
