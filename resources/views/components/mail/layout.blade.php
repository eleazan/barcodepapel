@props([
    'titulo' => null,
    'preheader' => null,
    'motivo' => null,
])

@php
    $tienda = config('tienda');
    // Los clientes de correo no aplican hojas de estilo: todo va en línea.
    $fondo     = '#faf8f5';
    $tinta     = '#374151';
    $suave     = '#6b7280';
    $marca     = '#00b5b5';
    $marcaOsc  = '#0e7490';
    $navy      = '#1a4e6a';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ?? $tienda['nombre'] }}</title>
</head>
<body style="margin:0; padding:0; background-color:{{ $fondo }}; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; color:{{ $tinta }};">

    @if ($preheader)
        {{-- Resumen que muestran las bandejas de entrada antes de abrir el correo --}}
        <div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ $preheader }}</div>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:{{ $fondo }};">
        <tr>
            <td align="center" style="padding:32px 16px;">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; background-color:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #eae6e0;">

                    {{-- Cabecera --}}
                    <tr>
                        <td align="center" style="background-color:{{ $navy }}; padding:28px 24px;">
                            <a href="{{ route('home') }}" style="text-decoration:none;">
                                <span style="font-size:22px; font-weight:bold; color:#ffffff; letter-spacing:0.5px;">{{ $tienda['nombre'] }}</span>
                            </a>
                            <div style="margin-top:6px; font-size:12px; color:{{ $marca }};">Librería en {{ $tienda['direccion']['ciudad'] }}</div>
                        </td>
                    </tr>

                    {{-- Contenido --}}
                    <tr>
                        <td style="padding:32px 28px; font-size:15px; line-height:1.6; color:{{ $tinta }};">
                            @if ($titulo)
                                <h1 style="margin:0 0 20px; font-size:21px; line-height:1.3; color:#111827; font-weight:600;">{{ $titulo }}</h1>
                            @endif

                            {{ $slot }}
                        </td>
                    </tr>

                    {{-- Pie --}}
                    <tr>
                        <td style="background-color:#f9fafb; padding:22px 28px; border-top:1px solid #eef2f5; font-size:12px; line-height:1.6; color:{{ $suave }};">
                            <strong style="color:{{ $tinta }};">{{ $tienda['nombre'] }}</strong><br>
                            {{ $tienda['direccion']['calle'] }} · {{ $tienda['direccion']['codigo_postal'] }} {{ $tienda['direccion']['ciudad'] }}<br>
                            <a href="tel:{{ $tienda['telefono']['enlace'] }}" style="color:{{ $marcaOsc }}; text-decoration:none;">{{ $tienda['telefono']['display'] }}</a>
                            ·
                            <a href="mailto:{{ $tienda['email'] }}" style="color:{{ $marcaOsc }}; text-decoration:none;">{{ $tienda['email'] }}</a>

                            <div style="margin-top:14px; padding-top:14px; border-top:1px solid #eef2f5;">
                                <a href="{{ route('terms') }}" style="color:{{ $suave }}; text-decoration:underline;">Condiciones de venta</a>
                                &nbsp;·&nbsp;
                                <a href="{{ route('privacy') }}" style="color:{{ $suave }}; text-decoration:underline;">Privacidad</a>
                                &nbsp;·&nbsp;
                                <a href="{{ route('delivery') }}" style="color:{{ $suave }}; text-decoration:underline;">Zonas de reparto</a>
                            </div>
                        </td>
                    </tr>
                </table>

                <div style="max-width:600px; margin-top:16px; font-size:11px; color:#9ca3af; text-align:center;">
                    Recibes este correo porque {{ $motivo ?? 'has hecho un pedido o tienes una cuenta en nuestra tienda' }}.
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
