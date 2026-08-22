@php
    $tienda  = config('tienda');
    $legal   = $tienda['legal'];
    $titular = $legal['razon_social'] ?: $tienda['nombre'];
@endphp

<x-store.legal-page
    titulo="Política de privacidad"
    :descripcion="'Cómo trata ' . $tienda['nombre'] . ' los datos personales de sus clientes: finalidades, plazos, destinatarios y derechos.'"
>

    <p>
        En {{ $tienda['nombre'] }} tratamos los datos que nos das para poder prepararte el pedido y llevarlo a tu casa.
        Nada m&aacute;s. No vendemos ni cedemos tus datos a terceros con fines comerciales.
    </p>

    <h2>1. Responsable del tratamiento</h2>

    <ul>
        <li><strong>Responsable:</strong> {{ $titular }}</li>
        @if ($legal['nif'])
            <li><strong>NIF:</strong> {{ $legal['nif'] }}</li>
        @endif
        <li>
            <strong>Direcci&oacute;n:</strong>
            {{ $tienda['direccion']['calle'] }}, {{ $tienda['direccion']['codigo_postal'] }}
            {{ $tienda['direccion']['ciudad'] }}, {{ $tienda['direccion']['provincia'] }}
        </li>
        <li><strong>Correo de contacto:</strong> <a href="mailto:{{ $tienda['email'] }}">{{ $tienda['email'] }}</a></li>
        <li><strong>Tel&eacute;fono:</strong> <a href="tel:{{ $tienda['telefono']['enlace'] }}">{{ $tienda['telefono']['display'] }}</a></li>
    </ul>

    <h2>2. Qu&eacute; datos tratamos</h2>

    <ul>
        <li><strong>Datos de pedido:</strong> nombre y apellidos, tel&eacute;fono, correo electr&oacute;nico (opcional), direcci&oacute;n de entrega, c&oacute;digo postal e indicaciones para el reparto.</li>
        <li><strong>Datos de la compra:</strong> productos, cantidades, importes y estado del pedido.</li>
        <li><strong>Datos de cuenta,</strong> si decides registrarte: nombre, correo electr&oacute;nico y contrase&ntilde;a cifrada.</li>
        <li><strong>Datos de navegaci&oacute;n:</strong> los estrictamente necesarios para que el sitio funcione (sesi&oacute;n y carrito), adem&aacute;s de los que autorices en el aviso de cookies.</li>
    </ul>

    <p>
        Los datos los facilitas t&uacute; mismo a trav&eacute;s de los formularios del sitio. Los campos marcados como
        obligatorios lo son porque sin ellos no podemos entregarte el pedido.
    </p>

    <h2>3. Para qu&eacute; los usamos y con qu&eacute; legitimaci&oacute;n</h2>

    <ul>
        <li>
            <strong>Gestionar, preparar y entregar tu pedido,</strong> y avisarte de su estado por correo o
            tel&eacute;fono. <br>
            <em>Base jur&iacute;dica:</em> ejecuci&oacute;n del contrato de compraventa (art. 6.1.b RGPD).
        </li>
        <li>
            <strong>Cumplir nuestras obligaciones contables y fiscales,</strong> incluida la emisi&oacute;n de facturas. <br>
            <em>Base jur&iacute;dica:</em> cumplimiento de una obligaci&oacute;n legal (art. 6.1.c RGPD).
        </li>
        <li>
            <strong>Atender tus consultas, incidencias, devoluciones y garant&iacute;as.</strong> <br>
            <em>Base jur&iacute;dica:</em> ejecuci&oacute;n del contrato e inter&eacute;s leg&iacute;timo en atender al cliente (art. 6.1.b y 6.1.f RGPD).
        </li>
        <li>
            <strong>Gestionar tu cuenta de usuario,</strong> si te registras. <br>
            <em>Base jur&iacute;dica:</em> ejecuci&oacute;n del contrato a tu solicitud (art. 6.1.b RGPD).
        </li>
    </ul>

    <p>
        No enviamos comunicaciones comerciales por correo electr&oacute;nico salvo que nos lo pidas expresamente, ni
        tomamos decisiones automatizadas que produzcan efectos jur&iacute;dicos sobre ti.
    </p>

    <h2>4. A qui&eacute;n comunicamos tus datos</h2>

    <p>Solo a quien hace falta para que el pedido llegue y quede correctamente facturado:</p>

    <ul>
        <li><strong>Nuestro sistema de gesti&oacute;n comercial (ERP)</strong>, que act&uacute;a como encargado del tratamiento y en el que se registran el cliente, el pedido y la factura.</li>
        <li><strong>El proveedor de alojamiento web y de correo electr&oacute;nico</strong>, como encargados del tratamiento, para que el sitio funcione y para enviarte la confirmaci&oacute;n del pedido.</li>
        <li><strong>Las administraciones p&uacute;blicas</strong>, cuando exista una obligaci&oacute;n legal de facilitarlos.</li>
    </ul>

    <p>
        El reparto lo hacemos nosotros mismos: <strong>no cedemos tu direcci&oacute;n a ninguna empresa de
        paqueter&iacute;a externa</strong>. Con todos los encargados del tratamiento mantenemos el contrato exigido por
        el art&iacute;culo 28 del RGPD. No est&aacute;n previstas transferencias internacionales de datos fuera del
        Espacio Econ&oacute;mico Europeo; si en alg&uacute;n momento fueran necesarias, se realizar&aacute;n con las
        garant&iacute;as adecuadas y se informar&aacute; de ello.
    </p>

    <h2>5. Cu&aacute;nto tiempo los conservamos</h2>

    <ul>
        <li><strong>Datos del pedido:</strong> mientras dure la relaci&oacute;n comercial y, despu&eacute;s, bloqueados durante los plazos de prescripci&oacute;n de las acciones legales y de las obligaciones mercantiles y fiscales (hasta 6 a&ntilde;os).</li>
        <li><strong>Datos de la cuenta de usuario:</strong> mientras la mantengas activa. Si la das de baja, se eliminan salvo los vinculados a pedidos ya realizados.</li>
        <li><strong>Consultas por correo o tel&eacute;fono:</strong> el tiempo necesario para resolverlas y un a&ntilde;o m&aacute;s.</li>
    </ul>

    <h2>6. Tus derechos</h2>

    <p>Puedes ejercer en cualquier momento los siguientes derechos:</p>

    <ul>
        <li><strong>Acceso:</strong> saber qu&eacute; datos tuyos tratamos.</li>
        <li><strong>Rectificaci&oacute;n:</strong> corregir los datos inexactos.</li>
        <li><strong>Supresi&oacute;n:</strong> pedir que los borremos cuando ya no sean necesarios.</li>
        <li><strong>Oposici&oacute;n</strong> y <strong>limitaci&oacute;n</strong> del tratamiento.</li>
        <li><strong>Portabilidad:</strong> recibir tus datos en un formato estructurado y de uso com&uacute;n.</li>
        <li><strong>Retirar el consentimiento</strong> que hubieras dado, sin que ello afecte a la licitud del tratamiento previo.</li>
    </ul>

    <p>
        Para ejercerlos, escr&iacute;benos a <a href="mailto:{{ $tienda['email'] }}">{{ $tienda['email'] }}</a>
        indicando qu&eacute; derecho quieres ejercer, o ac&eacute;rcate a nuestra tienda en
        {{ $tienda['direccion']['calle'] }}. Podemos pedirte que acredites tu identidad. La respuesta es gratuita y se
        da en el plazo de un mes.
    </p>

    <p>
        Si consideras que no hemos atendido correctamente tu solicitud, puedes presentar una reclamaci&oacute;n ante la
        <strong>Agencia Espa&ntilde;ola de Protecci&oacute;n de Datos</strong> (C/ Jorge Juan, 6, 28001 Madrid &mdash;
        <a href="https://www.aepd.es" target="_blank" rel="noopener noreferrer">www.aepd.es</a>).
    </p>

    <h2>7. Seguridad</h2>

    <p>
        Aplicamos las medidas t&eacute;cnicas y organizativas necesarias para proteger tus datos: conexi&oacute;n
        cifrada (HTTPS), contrase&ntilde;as almacenadas cifradas, acceso al panel de gesti&oacute;n restringido al
        personal autorizado y copias de seguridad peri&oacute;dicas.
    </p>

    <h2>8. Cookies</h2>

    <p>
        Usamos cookies t&eacute;cnicas imprescindibles para que el sitio funcione: mantener tu sesi&oacute;n, recordar
        el contenido del carrito y proteger los formularios. Estas cookies no requieren consentimiento. Cualquier otra
        cookie que pudi&eacute;ramos incorporar se instalar&aacute; &uacute;nicamente si la aceptas en el aviso que
        aparece al entrar, y podr&aacute;s cambiar tu decisi&oacute;n borrando las cookies desde tu navegador.
    </p>

    <h2>9. Menores de edad</h2>

    <p>
        El sitio est&aacute; dirigido a mayores de 18 a&ntilde;os. No recogemos conscientemente datos de menores sin la
        autorizaci&oacute;n de quienes ejerzan su patria potestad o tutela.
    </p>

    <h2>10. Cambios en esta pol&iacute;tica</h2>

    <p>
        Podemos actualizar esta pol&iacute;tica para adaptarla a novedades legales o a cambios en el funcionamiento de
        la tienda. La versi&oacute;n vigente es siempre la publicada en esta p&aacute;gina, con su fecha de
        &uacute;ltima actualizaci&oacute;n.
    </p>

</x-store.legal-page>
