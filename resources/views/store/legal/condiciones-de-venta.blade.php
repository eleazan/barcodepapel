@php
    $tienda  = config('tienda');
    $legal   = $tienda['legal'];
    $titular = $legal['razon_social'] ?: $tienda['nombre'];
@endphp

<x-store.legal-page
    titulo="Condiciones de venta y reparto"
    :descripcion="'Condiciones de compra en ' . $tienda['nombre'] . ': zonas de reparto en Ibiza, precios, pago contra entrega, plazos y devoluciones.'"
>

    <h2>1. Qui&eacute;nes somos</h2>

    <p>
        Estas condiciones regulan la venta de productos a trav&eacute;s de esta web por parte de {{ $titular }}@if ($legal['nif']), con NIF {{ $legal['nif'] }}@endif,
        con domicilio en {{ $tienda['direccion']['calle'] }}, {{ $tienda['direccion']['codigo_postal'] }}
        {{ $tienda['direccion']['ciudad'] }} ({{ $tienda['direccion']['provincia'] }}), que opera bajo el nombre
        comercial <strong>{{ $tienda['nombre'] }}</strong>.
    </p>

    <p>
        Puedes contactar con nosotros en <a href="mailto:{{ $tienda['email'] }}">{{ $tienda['email'] }}</a> o en el
        tel&eacute;fono <a href="tel:{{ $tienda['telefono']['enlace'] }}">{{ $tienda['telefono']['display'] }}</a>.
    </p>

    <h2>2. &Aacute;mbito de reparto</h2>

    <p>
        <strong>Repartimos nosotros mismos y solo en la isla de Ibiza.</strong> No trabajamos con empresas de
        paqueter&iacute;a externa, por lo que &uacute;nicamente podemos aceptar pedidos con entrega en los
        c&oacute;digos postales que tenemos dados de alta. Puedes consultarlos en la p&aacute;gina de
        <a href="{{ route('delivery') }}">zonas de reparto</a> o comprobar el tuyo directamente en el carrito.
    </p>

    <p>
        Si tu c&oacute;digo postal no est&aacute; cubierto, el sistema no permitir&aacute; finalizar el pedido.
        Escr&iacute;benos y buscamos una soluci&oacute;n.
    </p>

    <h2>3. Qui&eacute;n puede comprar</h2>

    <p>
        Para realizar un pedido debes ser mayor de 18 a&ntilde;os y facilitar datos veraces, en especial la
        direcci&oacute;n de entrega y un tel&eacute;fono de contacto operativo, que usamos para coordinar el reparto.
        No es necesario registrarse: puedes comprar como invitado.
    </p>

    <h2>4. Productos y disponibilidad</h2>

    <p>
        Mostramos el cat&aacute;logo con la informaci&oacute;n y las im&aacute;genes que nos facilitan editoriales y
        proveedores. Las im&aacute;genes son orientativas y pueden diferir ligeramente del producto recibido
        (ediciones, encuadernaciones o reimpresiones distintas).
    </p>

    <p>
        La disponibilidad se actualiza de forma autom&aacute;tica, pero puede variar mientras completas tu pedido. Si
        alguna unidad se agota antes de que confirmes, te lo indicamos en pantalla y ajustamos el pedido antes de
        registrarlo. Si detectamos la falta de existencias despu&eacute;s de confirmarlo, te llamamos para ofrecerte
        una alternativa o anular esa l&iacute;nea sin coste.
    </p>

    <h2>5. Precios</h2>

    <ul>
        <li>Todos los precios se muestran en <strong>euros (&euro;) e incluyen el IVA</strong> aplicable a cada producto.</li>
        <li>Los <strong>gastos de reparto</strong> se calculan seg&uacute;n el c&oacute;digo postal de entrega y se muestran antes de confirmar el pedido, sumados aparte del subtotal.</li>
        <li>El precio aplicable es el <strong>vigente en el momento en que confirmas el pedido</strong>. Si el precio de un producto cambia mientras rellenas el formulario, te avisamos del cambio y no registramos el pedido hasta que vuelvas a aceptarlo.</li>
        <li>El precio de venta de los libros se rige por lo dispuesto en la Ley 10/2007, de la lectura, del libro y de las bibliotecas.</li>
    </ul>

    <h2>6. C&oacute;mo se formaliza el pedido</h2>

    <ol>
        <li>A&ntilde;ades los productos al carrito y compruebas que tu c&oacute;digo postal tiene reparto.</li>
        <li>Rellenas tus datos de contacto y entrega y aceptas estas condiciones.</li>
        <li>Al pulsar <em>Confirmar pedido</em> queda registrado con estado <strong>pendiente</strong> y se te asigna un n&uacute;mero de pedido.</li>
        <li>Si nos has dejado tu correo, recibes un acuse de recibo con el detalle. Ese correo confirma la recepci&oacute;n del pedido y perfecciona el contrato.</li>
        <li>Preparamos el pedido y te contactamos para acordar el momento de la entrega.</li>
    </ol>

    <p>
        Guarda tu n&uacute;mero de pedido: es la referencia para cualquier consulta. Conservamos el documento
        electr&oacute;nico del contrato y puedes solicitarnos una copia.
    </p>

    <h2>7. Formas de pago</h2>

    <p>
        <strong>El pago se realiza en el momento de la entrega</strong>, en efectivo o con tarjeta ante nuestro
        repartidor. <strong>No se realiza ning&uacute;n cobro a trav&eacute;s de la web</strong>: no disponemos de
        pasarela de pago y nunca te pediremos los datos de tu tarjeta por internet, por correo ni por tel&eacute;fono.
    </p>

    <h2>8. Entrega</h2>

    <ul>
        <li>El plazo habitual de entrega es de <strong>24 a 48 horas laborables</strong> desde la confirmaci&oacute;n del pedido, salvo productos bajo pedido a editorial, de los que te informaremos.</li>
        <li>La entrega se realiza en la direcci&oacute;n que hayas indicado. Es importante que el tel&eacute;fono facilitado est&eacute; operativo.</li>
        <li>Si no hay nadie en el momento acordado, te llamamos para fijar un nuevo intento sin coste adicional.</li>
        <li>El riesgo de p&eacute;rdida o deterioro de los productos se transmite en el momento de la entrega.</li>
    </ul>

    <h2>9. Derecho de desistimiento</h2>

    <p>
        Como consumidor tienes derecho a desistir de la compra en un plazo de <strong>14 d&iacute;as naturales</strong>
        desde la recepci&oacute;n del pedido, sin necesidad de justificar tu decisi&oacute;n y sin penalizaci&oacute;n,
        conforme a los art&iacute;culos 102 y siguientes del Real Decreto Legislativo 1/2007.
    </p>

    <p>
        Para ejercerlo, comun&iacute;canoslo por cualquier medio inequ&iacute;voco &mdash;correo a
        <a href="mailto:{{ $tienda['email'] }}">{{ $tienda['email'] }}</a>, tel&eacute;fono o en la propia tienda&mdash;
        indicando tu n&uacute;mero de pedido. Puedes devolver los productos en nuestro local o acordar con nosotros la
        recogida.
    </p>

    <p>
        Devolveremos el importe abonado, incluidos los gastos de reparto est&aacute;ndar, en un plazo m&aacute;ximo de
        14 d&iacute;as naturales desde que tengamos constancia de tu desistimiento, por el mismo medio en que pagaste,
        pudiendo retenerlo hasta haber recibido los productos. Los productos deben devolverse en un estado que permita
        su nueva venta; respondes de la disminuci&oacute;n de valor derivada de una manipulaci&oacute;n distinta de la
        necesaria para comprobarlos.
    </p>

    <p>
        <strong>Excepciones.</strong> No cabe desistimiento en los supuestos del art&iacute;culo 103 del RDL 1/2007,
        entre otros: productos confeccionados conforme a tus especificaciones o claramente personalizados,
        grabaciones sonoras o de v&iacute;deo y programas inform&aacute;ticos precintados que hayan sido
        desprecintados, y prensa peri&oacute;dica y revistas (salvo suscripciones).
    </p>

    <h2>10. Garant&iacute;a y productos defectuosos</h2>

    <p>
        Los productos cuentan con la garant&iacute;a legal de conformidad de <strong>tres a&ntilde;os</strong> desde la
        entrega. Si un art&iacute;culo llega da&ntilde;ado, incompleto o no se corresponde con lo pedido,
        av&iacute;sanos cuanto antes: lo sustituimos o te devolvemos el importe sin coste para ti. Puedes reclamar la
        reparaci&oacute;n, la sustituci&oacute;n, la rebaja del precio o la resoluci&oacute;n del contrato en los
        t&eacute;rminos previstos por la ley.
    </p>

    <h2>11. Facturaci&oacute;n</h2>

    <p>
        Con la entrega recibes el albar&aacute;n del pedido. Si necesitas <strong>factura</strong>, ind&iacute;canoslo
        en las observaciones del pedido o solicit&aacute;ndola por correo con tus datos fiscales: te la emitimos y te
        la enviamos sin coste.
    </p>

    <h2>12. Reclamaciones y resoluci&oacute;n de conflictos</h2>

    <p>
        Puedes dirigir cualquier reclamaci&oacute;n a
        <a href="mailto:{{ $tienda['email'] }}">{{ $tienda['email'] }}</a> o al tel&eacute;fono
        <a href="tel:{{ $tienda['telefono']['enlace'] }}">{{ $tienda['telefono']['display'] }}</a>. Nos
        comprometemos a responderte en el plazo m&aacute;s breve posible. Disponemos de hojas oficiales de
        reclamaci&oacute;n a tu disposici&oacute;n en el establecimiento.
    </p>

    <p>
        Como consumidor puedes acudir a los servicios de consumo de tu municipio o de la Direcci&oacute;n General de
        Consumo del Govern de les Illes Balears, as&iacute; como al sistema arbitral de consumo cuando ambas partes lo
        acepten.
    </p>

    <h2>13. Nulidad parcial y legislaci&oacute;n aplicable</h2>

    <p>
        Si alguna cl&aacute;usula de estas condiciones fuera declarada nula, el resto continuar&aacute; siendo
        v&aacute;lido. Estas condiciones se rigen por la legislaci&oacute;n espa&ntilde;ola, en particular por el Real
        Decreto Legislativo 1/2007, de defensa de los consumidores y usuarios, y por la Ley 34/2002, de servicios de la
        sociedad de la informaci&oacute;n. Para cualquier controversia ser&aacute;n competentes los juzgados del
        domicilio del consumidor.
    </p>

    <p>
        El tratamiento de tus datos personales se detalla en la
        <a href="{{ route('privacy') }}">pol&iacute;tica de privacidad</a>.
    </p>

</x-store.legal-page>
