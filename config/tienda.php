<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Datos de la tienda
|--------------------------------------------------------------------------
|
| Fuente única de los datos de contacto de Barco de Papel: se usan en el
| layout de la tienda (JSON-LD, metas geo, footer), en la página de contacto
| y en el albarán en PDF. Si cambia la dirección, el teléfono o el horario,
| se cambia solo aquí.
|
*/

return [
    'nombre' => 'Barco de Papel',

    'direccion' => [
        'calle'         => 'Carrer de Balears, 19',
        'codigo_postal' => '07800',
        'ciudad'        => 'Eivissa',
        'provincia'     => 'Illes Balears',
        'pais'          => 'España',
        'pais_codigo'   => 'ES',
    ],

    'telefono' => [
        // Formato E.164 para los enlaces tel: y el JSON-LD
        'enlace' => '+34623199837',
        // Formato legible para mostrar en pantalla
        'display' => '+34 623 199 837',
    ],

    'email' => 'info@barcodepapel.es',

    /*
     | Datos del titular para el aviso legal, la política de privacidad y las
     | condiciones de venta. Son obligatorios por la LSSI-CE y el RGPD: hay que
     | rellenar `razon_social` y `nif` antes de abrir la tienda al público. Las
     | páginas legales omiten los campos que estén vacíos.
     */
    'legal' => [
        // Nombre fiscal de la persona física o jurídica titular del negocio.
        'razon_social' => null,
        'nif'          => null,
        // Datos de inscripción registral, solo si la titular es una sociedad.
        'registro' => null,
        // Fecha de la última revisión de los textos legales.
        'actualizado' => '2026-08-22',
    ],

    // Coordenadas del local (JSON-LD, metas geo y mapa de la página de contacto)
    'geo' => [
        'latitud'  => 38.9091633,
        'longitud' => 1.4229718,
    ],

    /*
     | Horario de apertura. `dias` usa los nombres de schema.org (JSON-LD),
     | `etiqueta` se muestra en la página de contacto y `etiqueta_corta` en el
     | footer. Un tramo sin `abre` se considera cerrado y no se publica en el
     | JSON-LD ni en el footer.
     */
    'horario' => [
        [
            'etiqueta'       => 'Lunes – Viernes',
            'etiqueta_corta' => 'Lun–Vie',
            'dias'           => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'abre'           => '09:30',
            'cierra'         => '20:00',
        ],
        [
            'etiqueta'       => 'Sábado',
            'etiqueta_corta' => 'Sábado',
            'dias'           => ['Saturday'],
            'abre'           => '10:00',
            'cierra'         => '14:00',
        ],
        [
            'etiqueta'       => 'Domingo',
            'etiqueta_corta' => 'Domingo',
            'dias'           => ['Sunday'],
            'abre'           => null,
            'cierra'         => null,
        ],
    ],
];
