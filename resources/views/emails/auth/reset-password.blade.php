<x-mail.layout
    titulo="Recupera tu contraseña"
    preheader="Enlace para elegir una contraseña nueva"
    motivo="alguien ha pedido restablecer la contraseña de tu cuenta"
>
    <p style="margin:0 0 16px;">¡Hola, {{ $nombre }}!</p>

    <p style="margin:0 0 16px;">
        Hemos recibido una solicitud para cambiar la contraseña de tu cuenta. Pulsa el botón para elegir
        una nueva.
    </p>

    <x-mail.boton :url="$url">
        Elegir contraseña nueva
    </x-mail.boton>

    @if ($minutos)
        <p style="margin:0 0 16px; font-size:14px; color:#6b7280;">
            El enlace caduca en {{ $minutos }} minutos.
        </p>
    @endif

    <p style="margin:0 0 16px; font-size:14px; color:#6b7280;">
        Si el botón no funciona, copia esta dirección en tu navegador:<br>
        <span style="word-break:break-all; color:#0e7490;">{{ $url }}</span>
    </p>

    <p style="margin:0; font-size:14px; color:#6b7280;">
        Si no has pedido tú el cambio, ignora este correo: tu contraseña seguirá siendo la misma.
    </p>
</x-mail.layout>
