<x-mail.layout
    titulo="Confirma tu correo"
    preheader="Un último paso para activar tu cuenta"
    motivo="te has registrado en nuestra tienda"
>
    <p style="margin:0 0 16px;">¡Hola, {{ $nombre }}!</p>

    <p style="margin:0 0 16px;">
        Gracias por crear una cuenta en {{ config('tienda.nombre') }}. Solo nos falta comprobar que este
        correo es tuyo: pulsa el botón y listo.
    </p>

    <x-mail.boton :url="$url">
        Confirmar mi correo
    </x-mail.boton>

    <p style="margin:0 0 16px; font-size:14px; color:#6b7280;">
        Si el botón no funciona, copia esta dirección en tu navegador:<br>
        <span style="word-break:break-all; color:#0e7490;">{{ $url }}</span>
    </p>

    <p style="margin:0; font-size:14px; color:#6b7280;">
        Si no has sido tú, puedes ignorar este correo: no se creará ninguna cuenta a tu nombre.
    </p>
</x-mail.layout>
