<x-mail.layout
    :titulo="'Hemos recibido tu pedido'"
    :preheader="'Pedido ' . $order->order_number . ' · ' . $order->formattedTotal()"
    motivo="has hecho un pedido en nuestra tienda"
>
    <p style="margin:0 0 16px;">¡Hola, {{ $order->customer_name }}!</p>

    <p style="margin:0 0 16px;">
        Ya tenemos tu pedido <strong>{{ $order->order_number }}</strong>. Lo estamos revisando y te avisamos
        en cuanto salga para el reparto.
    </p>

    @if ($order->formattedEstimatedDelivery())
        <p style="margin:0 0 16px;">
            Según los días de reparto de tu zona, te lo llevamos el
            <strong>{{ $order->formattedEstimatedDelivery() }}</strong>.
        </p>
    @endif

    <x-mail.pedido-detalle :order="$order" />

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:20px 0 0; background-color:#fffbeb; border:1px solid #fde68a; border-radius:12px;">
        <tr>
            <td style="padding:14px 18px; font-size:14px; line-height:1.6; color:#92400e;">
                El pago se realiza <strong>en el momento de la entrega</strong>, en efectivo o con tarjeta.
                No hemos hecho ningún cargo online. Los importes incluyen el IVA.
            </td>
        </tr>
    </table>

    <x-mail.boton :url="route('checkout.confirmation', $order->order_number)">
        Ver mi pedido
    </x-mail.boton>

    <p style="margin:0; font-size:14px; color:#6b7280;">
        Si algo no cuadra, responde a este correo o llámanos al {{ config('tienda.telefono.display') }}.
        Gracias por comprar en tu librería de barrio.
    </p>
</x-mail.layout>
