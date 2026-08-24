<x-mail.layout
    :titulo="'Nuevo pedido ' . $order->order_number"
    :preheader="$order->customer_name . ' · ' . $order->formattedTotal() . ' · CP ' . $order->postal_code"
    motivo="eres la tienda y ha entrado un pedido"
>
    <p style="margin:0 0 20px;">
        Ha entrado un pedido nuevo por la web. Está en estado
        <strong>{{ $order->statusLabel() }}</strong>; entra en Verial cuando lo marques como preparado.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px; background-color:#f9fafb; border-radius:12px;">
        <tr>
            <td style="padding:16px 18px; font-size:14px; line-height:1.7; color:#374151;">
                <strong style="color:#111827;">Cliente</strong><br>
                {{ $order->customer_name }}<br>
                <a href="tel:{{ $order->customer_phone }}" style="color:#0e7490;">{{ $order->customer_phone }}</a>
                @if ($order->customer_email)
                    <br><a href="mailto:{{ $order->customer_email }}" style="color:#0e7490;">{{ $order->customer_email }}</a>
                @else
                    <br><span style="color:#b45309;">Sin correo: avisar por teléfono.</span>
                @endif
            </td>
        </tr>
    </table>

    <x-mail.pedido-detalle :order="$order" />

    <x-mail.boton :url="route('admin.orders.show', $order)">
        Abrir en el panel
    </x-mail.boton>
</x-mail.layout>
