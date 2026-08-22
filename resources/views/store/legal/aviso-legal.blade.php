@php
    $tienda   = config('tienda');
    $legal    = $tienda['legal'];
    $titular  = $legal['razon_social'] ?: $tienda['nombre'];
    $dominio  = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'barcodepapel.es';
@endphp

<x-store.legal-page
    titulo="Aviso legal"
    :descripcion="'Datos identificativos y condiciones de uso del sitio web de ' . $tienda['nombre'] . ', librería en Ibiza.'"
>

    <h2>1. Datos identificativos</h2>

    <p>
        En cumplimiento del art&iacute;culo 10 de la Ley 34/2002, de 11 de julio, de servicios de la sociedad de la
        informaci&oacute;n y de comercio electr&oacute;nico (LSSI-CE), se ponen a disposici&oacute;n de los usuarios
        los datos del titular de este sitio web:
    </p>

    <ul>
        <li><strong>Titular:</strong> {{ $titular }}</li>
        @if ($legal['nif'])
            <li><strong>NIF:</strong> {{ $legal['nif'] }}</li>
        @endif
        <li><strong>Nombre comercial:</strong> {{ $tienda['nombre'] }}</li>
        <li>
            <strong>Domicilio:</strong>
            {{ $tienda['direccion']['calle'] }}, {{ $tienda['direccion']['codigo_postal'] }}
            {{ $tienda['direccion']['ciudad'] }}, {{ $tienda['direccion']['provincia'] }} ({{ $tienda['direccion']['pais'] }})
        </li>
        <li><strong>Tel&eacute;fono:</strong> <a href="tel:{{ $tienda['telefono']['enlace'] }}">{{ $tienda['telefono']['display'] }}</a></li>
        <li><strong>Correo electr&oacute;nico:</strong> <a href="mailto:{{ $tienda['email'] }}">{{ $tienda['email'] }}</a></li>
        <li><strong>Sitio web:</strong> {{ $dominio }}</li>
        @if ($legal['registro'])
            <li><strong>Datos registrales:</strong> {{ $legal['registro'] }}</li>
        @endif
    </ul>

    <h2>2. Objeto</h2>

    <p>
        Este aviso legal regula el acceso y la utilizaci&oacute;n del sitio web {{ $dominio }}, a trav&eacute;s del cual
        {{ $tienda['nombre'] }} muestra su cat&aacute;logo de libros, papeler&iacute;a y material escolar, y permite
        realizar pedidos con reparto propio en la isla de Ibiza.
    </p>

    <p>
        La navegaci&oacute;n por el sitio atribuye la condici&oacute;n de usuario e implica la aceptaci&oacute;n plena
        de este aviso legal. Las compras se rigen adem&aacute;s por las
        <a href="{{ route('terms') }}">condiciones de venta y reparto</a>.
    </p>

    <h2>3. Condiciones de uso</h2>

    <p>El usuario se compromete a:</p>

    <ul>
        <li>Utilizar el sitio conforme a la ley, a la buena fe y al orden p&uacute;blico.</li>
        <li>No introducir contenidos que resulten difamatorios, injuriosos u obscenos, ni que vulneren derechos de terceros.</li>
        <li>No realizar actuaciones que da&ntilde;en, inutilicen o sobrecarguen el sitio, ni impidan su normal utilizaci&oacute;n.</li>
        <li>Facilitar datos veraces en los formularios, en particular la direcci&oacute;n de entrega y el tel&eacute;fono de contacto.</li>
    </ul>

    <h2>4. Propiedad intelectual e industrial</h2>

    <p>
        Los contenidos de este sitio —textos, fotograf&iacute;as, dise&ntilde;o gr&aacute;fico, c&oacute;digo fuente,
        logotipos y marcas— son titularidad de {{ $titular }} o de terceros que han autorizado su uso, y est&aacute;n
        protegidos por la normativa de propiedad intelectual e industrial.
    </p>

    <p>
        Las im&aacute;genes y descripciones de los productos pueden proceder de las editoriales, distribuidores o
        fabricantes correspondientes, y se utilizan con finalidad exclusivamente informativa y comercial. Las marcas
        citadas pertenecen a sus respectivos titulares.
    </p>

    <p>
        Queda prohibida la reproducci&oacute;n, distribuci&oacute;n, comunicaci&oacute;n p&uacute;blica o
        transformaci&oacute;n de los contenidos sin autorizaci&oacute;n expresa, salvo los usos permitidos por la ley.
    </p>

    <h2>5. Responsabilidad</h2>

    <p>
        {{ $titular }} procura mantener la informaci&oacute;n del cat&aacute;logo actualizada y libre de errores, pero no
        puede garantizar la ausencia de erratas en precios, disponibilidad o descripciones. Si se detecta un error
        manifiesto en el precio de un producto ya pedido, se informar&aacute; al cliente antes de la entrega y este
        podr&aacute; confirmar o anular el pedido sin coste alguno.
    </p>

    <p>
        No se garantiza la disponibilidad ininterrumpida del sitio ni la ausencia de fallos t&eacute;cnicos, si bien se
        emplean los medios razonables para evitarlos y corregirlos.
    </p>

    <h2>6. Enlaces a otros sitios</h2>

    <p>
        Este sitio puede incluir enlaces a p&aacute;ginas de terceros. {{ $titular }} no controla sus contenidos ni
        asume responsabilidad alguna sobre ellos; la inclusi&oacute;n de un enlace no implica recomendaci&oacute;n ni
        relaci&oacute;n con el sitio enlazado.
    </p>

    <h2>7. Protecci&oacute;n de datos y cookies</h2>

    <p>
        El tratamiento de los datos personales facilitados a trav&eacute;s del sitio se describe en la
        <a href="{{ route('privacy') }}">pol&iacute;tica de privacidad</a>, que incluye la informaci&oacute;n sobre el
        uso de cookies.
    </p>

    <h2>8. Legislaci&oacute;n aplicable</h2>

    <p>
        Este aviso legal se rige por la legislaci&oacute;n espa&ntilde;ola. Para la resoluci&oacute;n de cualquier
        controversia ser&aacute;n competentes los juzgados y tribunales que correspondan conforme a la normativa
        aplicable; trat&aacute;ndose de consumidores, los de su lugar de domicilio.
    </p>

    <h2>9. Contacto</h2>

    <p>
        Para cualquier duda sobre este aviso legal puedes escribir a
        <a href="mailto:{{ $tienda['email'] }}">{{ $tienda['email'] }}</a> o llamar al
        <a href="tel:{{ $tienda['telefono']['enlace'] }}">{{ $tienda['telefono']['display'] }}</a>.
    </p>

</x-store.legal-page>
