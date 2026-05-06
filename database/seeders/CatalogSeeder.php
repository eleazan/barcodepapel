<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // ----- Categorías -----
        $categories = collect([
            ['name' => 'Libros', 'slug' => 'libros', 'description' => 'Libros de todos los géneros', 'sort_order' => 1],
            ['name' => 'Cuadernos y Libretas', 'slug' => 'cuadernos-libretas', 'description' => 'Cuadernos profesionales, libretas y blocks', 'sort_order' => 2],
            ['name' => 'Papelería Escolar', 'slug' => 'papeleria-escolar', 'description' => 'Material escolar básico', 'sort_order' => 3],
            ['name' => 'Arte y Manualidades', 'slug' => 'arte-manualidades', 'description' => 'Materiales para dibujo, pintura y manualidades', 'sort_order' => 4],
            ['name' => 'Oficina', 'slug' => 'oficina', 'description' => 'Insumos y accesorios de oficina', 'sort_order' => 5],
            ['name' => 'Mochilas y Loncheras', 'slug' => 'mochilas-loncheras', 'description' => 'Mochilas, loncheras y estuches', 'sort_order' => 6],
        ])->map(fn ($data) => Category::create($data + ['is_active' => true]));

        $catMap = $categories->keyBy('slug');

        // ----- Productos -----
        $products = [
            // Libros
            ['category' => 'libros', 'name' => 'El Principito — Antoine de Saint-Exupéry', 'sku' => 'LIB-001', 'price' => 9.95, 'stock' => 25],
            ['category' => 'libros', 'name' => 'Cien años de soledad — Gabriel García Márquez', 'sku' => 'LIB-002', 'price' => 14.90, 'stock' => 15],
            ['category' => 'libros', 'name' => 'Harry Potter y la piedra filosofal', 'sku' => 'LIB-003', 'price' => 18.50, 'stock' => 20],
            ['category' => 'libros', 'name' => 'Don Quijote de la Mancha (edición escolar)', 'sku' => 'LIB-004', 'price' => 8.50, 'stock' => 12],
            ['category' => 'libros', 'name' => 'Diario de Greg 1 — Jeff Kinney', 'sku' => 'LIB-005', 'price' => 12.95, 'stock' => 18],
            ['category' => 'libros', 'name' => 'Diccionario Larousse Español', 'sku' => 'LIB-006', 'price' => 11.50, 'stock' => 10],
            ['category' => 'libros', 'name' => 'Atlas Universal Ilustrado', 'sku' => 'LIB-007', 'price' => 19.90, 'stock' => 8],

            // Cuadernos
            ['category' => 'cuadernos-libretas', 'name' => 'Cuaderno Oxford A4 80 hojas', 'sku' => 'CUA-001', 'price' => 3.20, 'stock' => 80],
            ['category' => 'cuadernos-libretas', 'name' => 'Cuaderno Oxford A4 160 hojas', 'sku' => 'CUA-002', 'price' => 5.50, 'stock' => 60],
            ['category' => 'cuadernos-libretas', 'name' => 'Libreta grapada A5 cuadrícula', 'sku' => 'CUA-003', 'price' => 1.80, 'stock' => 100],
            ['category' => 'cuadernos-libretas', 'name' => 'Notas adhesivas Post-it (pack 5)', 'sku' => 'CUA-004', 'price' => 4.95, 'stock' => 40],
            ['category' => 'cuadernos-libretas', 'name' => 'Bloc de dibujo Canson A4', 'sku' => 'CUA-005', 'price' => 7.50, 'stock' => 30],

            // Papelería escolar
            ['category' => 'papeleria-escolar', 'name' => 'Lápices Staedtler Noris HB (caja 12)', 'sku' => 'ESC-001', 'price' => 4.50, 'stock' => 50],
            ['category' => 'papeleria-escolar', 'name' => 'Bolígrafos Bic Cristal azul (paq. 10)', 'sku' => 'ESC-002', 'price' => 3.95, 'stock' => 45],
            ['category' => 'papeleria-escolar', 'name' => 'Goma Milan 430', 'sku' => 'ESC-003', 'price' => 0.60, 'stock' => 120],
            ['category' => 'papeleria-escolar', 'name' => 'Sacapuntas metálico doble Staedtler', 'sku' => 'ESC-004', 'price' => 1.20, 'stock' => 90],
            ['category' => 'papeleria-escolar', 'name' => 'Regla Maped 30 cm transparente', 'sku' => 'ESC-005', 'price' => 1.10, 'stock' => 70],
            ['category' => 'papeleria-escolar', 'name' => 'Tijeras escolares Maped', 'sku' => 'ESC-006', 'price' => 2.50, 'stock' => 55],
            ['category' => 'papeleria-escolar', 'name' => 'Pegamento en barra Pritt 22g', 'sku' => 'ESC-007', 'price' => 2.10, 'stock' => 65],
            ['category' => 'papeleria-escolar', 'name' => 'Lápices de colores Faber-Castell 24 uds.', 'sku' => 'ESC-008', 'price' => 11.90, 'stock' => 3],
            ['category' => 'papeleria-escolar', 'name' => 'Rotuladores Carioca Joy (paq. 12)', 'sku' => 'ESC-009', 'price' => 5.50, 'stock' => 35],

            // Arte
            ['category' => 'arte-manualidades', 'name' => 'Acuarelas Pelikan 12 colores', 'sku' => 'ART-001', 'price' => 6.90, 'stock' => 25],
            ['category' => 'arte-manualidades', 'name' => 'Pinceles redondos set de 6', 'sku' => 'ART-002', 'price' => 5.95, 'stock' => 20],
            ['category' => 'arte-manualidades', 'name' => 'Papel crepé surtido (paq. 10)', 'sku' => 'ART-003', 'price' => 3.50, 'stock' => 40],
            ['category' => 'arte-manualidades', 'name' => 'Plastilina Jovi 10 barras', 'sku' => 'ART-004', 'price' => 4.75, 'stock' => 30],
            ['category' => 'arte-manualidades', 'name' => 'Lienzo para pintura 40x50 cm', 'sku' => 'ART-005', 'price' => 8.90, 'stock' => 15],

            // Oficina
            ['category' => 'oficina', 'name' => 'Resma papel Navigator A4 (500 hojas)', 'sku' => 'OFI-001', 'price' => 7.50, 'stock' => 40],
            ['category' => 'oficina', 'name' => 'Carpeta de anillas A4', 'sku' => 'OFI-002', 'price' => 3.90, 'stock' => 30],
            ['category' => 'oficina', 'name' => 'Grapadora Petrus medio tira', 'sku' => 'OFI-003', 'price' => 6.50, 'stock' => 20],
            ['category' => 'oficina', 'name' => 'Clips estándar (caja 100)', 'sku' => 'OFI-004', 'price' => 1.20, 'stock' => 50],
            ['category' => 'oficina', 'name' => 'Cinta adhesiva Tesa transparente 19mm', 'sku' => 'OFI-005', 'price' => 1.50, 'stock' => 0],
            ['category' => 'oficina', 'name' => 'Subcarpetas Gio A4 (paq. 25)', 'sku' => 'OFI-006', 'price' => 4.90, 'stock' => 35],

            // Mochilas
            ['category' => 'mochilas-loncheras', 'name' => 'Mochila escolar Totto 17"', 'sku' => 'MOC-001', 'price' => 35.00, 'stock' => 10],
            ['category' => 'mochilas-loncheras', 'name' => 'Bolsa térmica infantil', 'sku' => 'MOC-002', 'price' => 14.90, 'stock' => 15],
            ['category' => 'mochilas-loncheras', 'name' => 'Estuche escolar doble cremallera', 'sku' => 'MOC-003', 'price' => 8.90, 'stock' => 25],
            ['category' => 'mochilas-loncheras', 'name' => 'Mochila con ruedas primaria', 'sku' => 'MOC-004', 'price' => 45.00, 'stock' => 5],
        ];

        $createdProducts = collect();
        foreach ($products as $p) {
            $cat = $catMap[$p['category']];
            unset($p['category']);
            $createdProducts->push(Product::create($p + [
                'category_id' => $cat->id,
                'is_active' => true,
            ]));
        }

        // ----- Zonas de reparto (CPs de Ibiza / Eivissa) -----
        $zones = [
            ['postal_code' => '07800', 'neighborhood' => 'Eivissa Centro (Dalt Vila)', 'city' => 'Eivissa', 'delivery_fee' => 0],
            ['postal_code' => '07801', 'neighborhood' => 'La Marina / Puerto', 'city' => 'Eivissa', 'delivery_fee' => 0],
            ['postal_code' => '07802', 'neighborhood' => 'Can Misses', 'city' => 'Eivissa', 'delivery_fee' => 2],
            ['postal_code' => '07803', 'neighborhood' => 'Ses Figueretes', 'city' => 'Eivissa', 'delivery_fee' => 2],
            ['postal_code' => '07810', 'neighborhood' => 'Jesús', 'city' => 'Santa Eulària', 'delivery_fee' => 3],
            ['postal_code' => '07814', 'neighborhood' => 'Santa Gertrudis', 'city' => 'Santa Eulària', 'delivery_fee' => 4],
            ['postal_code' => '07840', 'neighborhood' => 'Santa Eulària des Riu', 'city' => 'Santa Eulària', 'delivery_fee' => 5],
            ['postal_code' => '07849', 'neighborhood' => 'Es Canar', 'city' => 'Santa Eulària', 'delivery_fee' => 6],
            ['postal_code' => '07820', 'neighborhood' => 'Sant Antoni de Portmany', 'city' => 'Sant Antoni', 'delivery_fee' => 5],
            ['postal_code' => '07829', 'neighborhood' => 'Cala de Bou / San Agustín', 'city' => 'Sant Antoni', 'delivery_fee' => 6],
            ['postal_code' => '07830', 'neighborhood' => 'Sant Josep de sa Talaia', 'city' => 'Sant Josep', 'delivery_fee' => 5],
            ['postal_code' => '07817', 'neighborhood' => 'Sant Jordi de ses Salines', 'city' => 'Sant Josep', 'delivery_fee' => 3],
            ['postal_code' => '07818', 'neighborhood' => 'Platja d\'en Bossa', 'city' => 'Sant Josep', 'delivery_fee' => 3],
            ['postal_code' => '07815', 'neighborhood' => 'Sant Carles de Peralta', 'city' => 'Santa Eulària', 'delivery_fee' => 7],
            ['postal_code' => '07816', 'neighborhood' => 'Sant Llorenç de Balàfia', 'city' => 'Sant Joan', 'delivery_fee' => 7],
        ];

        foreach ($zones as $zone) {
            DeliveryZone::create($zone + ['is_active' => true]);
        }

        // ----- Pedidos de ejemplo -----
        $sampleOrders = [
            [
                'customer_name' => 'Maria Tur Marí',
                'customer_email' => 'maria.tur@email.com',
                'customer_phone' => '671234567',
                'delivery_address' => 'Carrer de la Virgen 18, 2ºA',
                'postal_code' => '07800',
                'status' => Order::STATUS_ENTREGADO,
                'delivery_fee' => 0,
                'notes' => null,
                'items' => [
                    ['sku' => 'LIB-001', 'qty' => 1],
                    ['sku' => 'CUA-001', 'qty' => 3],
                    ['sku' => 'ESC-002', 'qty' => 2],
                ],
            ],
            [
                'customer_name' => 'Toni Ribas Costa',
                'customer_email' => null,
                'customer_phone' => '698765432',
                'delivery_address' => 'Avinguda d\'Espanya 42',
                'postal_code' => '07820',
                'status' => Order::STATUS_EN_REPARTO,
                'delivery_fee' => 5,
                'notes' => 'Tocar timbre del bajo',
                'items' => [
                    ['sku' => 'LIB-003', 'qty' => 1],
                    ['sku' => 'LIB-005', 'qty' => 1],
                ],
            ],
            [
                'customer_name' => 'Catalina Ferrer Planells',
                'customer_email' => 'cati.ferrer@email.com',
                'customer_phone' => '611223344',
                'delivery_address' => 'Carrer Sant Jaume 7',
                'postal_code' => '07840',
                'status' => Order::STATUS_PREPARADO,
                'delivery_fee' => 5,
                'notes' => null,
                'items' => [
                    ['sku' => 'ESC-008', 'qty' => 2],
                    ['sku' => 'CUA-005', 'qty' => 1],
                    ['sku' => 'ART-001', 'qty' => 1],
                    ['sku' => 'ART-002', 'qty' => 1],
                ],
            ],
            [
                'customer_name' => 'Joan Serra Torres',
                'customer_email' => 'joan.serra@empresa.es',
                'customer_phone' => '644556677',
                'delivery_address' => 'Carrer Pere Francesc 15, 1er piso',
                'postal_code' => '07801',
                'status' => Order::STATUS_PENDIENTE,
                'delivery_fee' => 0,
                'notes' => 'Factura a nombre de Serra & Associats S.L.',
                'items' => [
                    ['sku' => 'OFI-001', 'qty' => 5],
                    ['sku' => 'OFI-002', 'qty' => 10],
                    ['sku' => 'OFI-004', 'qty' => 3],
                    ['sku' => 'OFI-006', 'qty' => 4],
                ],
            ],
            [
                'customer_name' => 'Neus Cardona Roig',
                'customer_email' => 'neus.cardona@email.com',
                'customer_phone' => '677889900',
                'delivery_address' => 'Carrer de sa Creu 22',
                'postal_code' => '07810',
                'status' => Order::STATUS_PENDIENTE,
                'delivery_fee' => 3,
                'notes' => null,
                'items' => [
                    ['sku' => 'MOC-001', 'qty' => 1],
                    ['sku' => 'MOC-003', 'qty' => 1],
                    ['sku' => 'ESC-001', 'qty' => 2],
                    ['sku' => 'ESC-003', 'qty' => 4],
                    ['sku' => 'CUA-001', 'qty' => 5],
                ],
            ],
            [
                'customer_name' => 'Vicent Escandell Noguera',
                'customer_email' => null,
                'customer_phone' => '633221100',
                'delivery_address' => 'Passeig de Vara de Rey 9',
                'postal_code' => '07800',
                'status' => Order::STATUS_ENTREGADO,
                'delivery_fee' => 0,
                'notes' => null,
                'items' => [
                    ['sku' => 'LIB-002', 'qty' => 1],
                    ['sku' => 'LIB-007', 'qty' => 1],
                ],
            ],
            [
                'customer_name' => 'CEIP Sa Bodega',
                'customer_email' => 'secretaria@ceipsabodega.es',
                'customer_phone' => '971301234',
                'delivery_address' => 'Carrer de Cas Serres 30',
                'postal_code' => '07802',
                'status' => Order::STATUS_PENDIENTE,
                'delivery_fee' => 0,
                'notes' => 'Pedido institucional — entregar en secretaría. Horario: 9h-14h.',
                'items' => [
                    ['sku' => 'CUA-001', 'qty' => 30],
                    ['sku' => 'ESC-001', 'qty' => 30],
                    ['sku' => 'ESC-003', 'qty' => 30],
                    ['sku' => 'ESC-007', 'qty' => 15],
                ],
            ],
            [
                'customer_name' => 'Margalida Costa Juan',
                'customer_email' => 'marga.art@email.com',
                'customer_phone' => '699001122',
                'delivery_address' => 'Carrer Major 5, Sant Carles',
                'postal_code' => '07815',
                'status' => Order::STATUS_EN_REPARTO,
                'delivery_fee' => 7,
                'notes' => null,
                'items' => [
                    ['sku' => 'ART-005', 'qty' => 3],
                    ['sku' => 'ART-002', 'qty' => 2],
                    ['sku' => 'CUA-005', 'qty' => 2],
                ],
            ],
        ];

        // Index products by SKU for lookup
        $productsBySku = $createdProducts->keyBy('sku');

        foreach ($sampleOrders as $i => $orderData) {
            $itemsData = $orderData['items'];
            unset($orderData['items']);

            $subtotal = 0;
            $orderItems = [];

            foreach ($itemsData as $item) {
                $product = $productsBySku[$item['sku']];
                $itemTotal = (float) $product->price * $item['qty'];
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['qty'],
                    'unit_price' => $product->price,
                    'total' => $itemTotal,
                ];
            }

            $orderData['subtotal'] = $subtotal;
            $orderData['total'] = $subtotal + $orderData['delivery_fee'];
            $orderData['created_at'] = now()->subDays(count($sampleOrders) - $i)->subHours(rand(0, 12));

            $order = Order::create($orderData);
            $order->items()->createMany($orderItems);
        }

        // ─── Blog posts ─────────────────────────────────────────────────────
        $admin = User::where('is_admin', true)->first();

        $posts = [
            [
                'title' => 'Novedades de primavera: libros que no te puedes perder',
                'excerpt' => 'Descubre las novedades literarias que han llegado a nuestra librería este mes. Desde narrativa contemporánea hasta los mejores álbumes ilustrados para los más pequeños.',
                'body' => '<p>La primavera ha llegado a Ibiza y con ella una selección increíble de nuevos títulos que ya están disponibles en nuestra librería.</p><h2>Narrativa</h2><p>Este mes destacamos la nueva novela de Irene Solà, que nos transporta a un mundo de realismo mágico mediterráneo. También ha llegado la esperadísima traducción de la última obra de Ocean Vuong, una prosa poética que no deja indiferente.</p><h2>Infantil y juvenil</h2><p>Para los más pequeños, tenemos los nuevos álbumes de la colección Andana, con ilustraciones preciosas que harán volar la imaginación. Y para los adolescentes, la nueva trilogía de fantasía que está arrasando en las listas de ventas.</p><h2>Material escolar</h2><p>Además, hemos renovado nuestra sección de material escolar con las últimas novedades de Faber-Castell y Staedtler. Colores vibrantes y herramientas de calidad para artistas de todas las edades.</p><p>¡Ven a descubrirlos o haz tu pedido con reparto a domicilio!</p>',
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Vuelta al cole 2026: guía completa de material escolar',
                'excerpt' => 'Todo lo que necesitas para la vuelta al cole en Ibiza. Cuadernos, mochilas, estuches y mucho más. Te lo llevamos a casa.',
                'body' => '<p>Septiembre está a la vuelta de la esquina y en Barco de Papel ya tenemos preparado todo el material escolar que tus hijos van a necesitar.</p><h2>Listas de material</h2><p>Si tu cole ya ha publicado la lista de material, tráenosla y te lo preparamos todo. También puedes enviárnosla por email o WhatsApp y te lo llevamos a casa.</p><h2>Lo más vendido</h2><ul><li>Mochilas Totto y Eastpak</li><li>Cuadernos Oxford y Lamela</li><li>Estuches Enso y Roll Road</li><li>Lápices de colores Faber-Castell</li><li>Rotuladores Carioca y Giotto</li></ul><h2>Reparto a domicilio</h2><p>Recuerda que hacemos reparto a domicilio en toda la isla de Ibiza. Haz tu pedido y te lo llevamos en 24-48 horas.</p>',
                'is_published' => true,
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Club de lectura: empezamos en octubre',
                'excerpt' => 'Lanzamos nuestro primer club de lectura. Cada mes elegiremos un libro y nos reuniremos para comentarlo. ¡Apúntate!',
                'body' => '<p>Estamos muy contentos de anunciar que en octubre arranca el <strong>Club de Lectura de Barco de Papel</strong>.</p><h2>¿Cómo funciona?</h2><p>Cada mes seleccionaremos un libro. Los participantes tendrán un 10% de descuento en el título elegido. Nos reuniremos el último viernes de cada mes en un lugar especial de Ibiza para compartir impresiones y debatir sobre la lectura.</p><h2>Primer libro</h2><p>El primer libro del club será anunciado en nuestras redes sociales y en esta misma web la primera semana de octubre. ¡Estate atento!</p><h2>Inscripción</h2><p>Para apuntarte, pásate por la librería o envíanos un email a info@barcodepapel.es. Es gratuito y abierto a todos los amantes de la lectura en Ibiza.</p>',
                'is_published' => true,
                'published_at' => now()->subDays(17),
            ],
            [
                'title' => 'Los 5 mejores libros para regalar esta Navidad',
                'excerpt' => 'Ideas de regalo para todos los gustos. Desde bestsellers hasta joyas escondidas que sorprenderán a cualquier lector.',
                'body' => '<p>La Navidad se acerca y un buen libro siempre es un regalo perfecto. Aquí van nuestras recomendaciones:</p><h2>1. Para el lector voraz</h2><p>"Intermezzo" de Sally Rooney — La autora irlandesa vuelve con una historia sobre dos hermanos que navegan el duelo y el amor de maneras completamente distintas.</p><h2>2. Para los peques</h2><p>"El monstruo de colores va al cole" — Un clásico moderno que ayuda a los niños a gestionar sus emociones ante los cambios.</p><h2>3. Para amantes de Ibiza</h2><p>"Eivissa: una historia" — Un recorrido ilustrado por la historia de nuestra isla, desde los fenicios hasta hoy.</p><h2>4. Para quien quiere aprender</h2><p>"Pensar rápido, pensar despacio" de Daniel Kahneman — Un libro que cambiará cómo entiendes tus propias decisiones.</p><h2>5. Para artistas</h2><p>Set de acuarelas Winsor & Newton con cuaderno de papel algodón — Perfecto para iniciarse en la acuarela o mejorar la técnica.</p>',
                'is_published' => true,
                'published_at' => now()->subDays(25),
            ],
        ];

        if ($admin) {
            foreach ($posts as $postData) {
                Post::create(array_merge($postData, ['user_id' => $admin->id]));
            }
        }
    }
}
