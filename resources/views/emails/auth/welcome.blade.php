<x-mail.layout
    :titulo="'Bienvenido a ' . config('tienda.nombre')"
    preheader="Tu cuenta ya está lista"
    motivo="acabas de crear una cuenta en nuestra tienda"
>
    <p style="margin:0 0 16px;">¡Hola, {{ $nombre }}!</p>

    <p style="margin:0 0 16px;">
        Tu cuenta ya está confirmada. A partir de ahora tus pedidos quedan guardados y no tendrás que
        volver a escribir tus datos de entrega cada vez.
    </p>

    <p style="margin:0 0 8px;">Un par de cosas que conviene saber:</p>

    <ul style="margin:0 0 20px; padding-left:20px; color:#374151;">
        <li style="margin-bottom:6px;">Repartimos nosotros mismos, solo en Ibiza. Comprueba tu código postal antes de pedir.</li>
        <li style="margin-bottom:6px;">Se paga en el momento de la entrega, en efectivo o con tarjeta. Nunca cobramos online.</li>
        <li>Cada zona tiene sus días de reparto; al finalizar el pedido te decimos qué día te llega.</li>
    </ul>

    <x-mail.boton :url="route('catalog')">
        Ver el catálogo
    </x-mail.boton>

    <p style="margin:0; font-size:14px; color:#6b7280;">
        Cualquier duda, responde a este correo o llámanos al {{ config('tienda.telefono.display') }}.
        Nos vemos por la librería.
    </p>
</x-mail.layout>
