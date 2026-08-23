# AGENTS.md — Barco de Papel

Instrucciones del proyecto para agentes de código. Fuente única: `CLAUDE.md`
solo importa este archivo, así que **los cambios se hacen aquí**.

## Proyecto

Sitio web para **Barco de Papel**, una librería en Ibiza (Eivissa) que muestra su catálogo de productos y vende con reparto propio en códigos postales de la isla (sin paquetería externa). Localizado en español (es), zona horaria Europe/Madrid.

## Stack

- **Backend:** Laravel 13, PHP 8.5+
- **Frontend:** Blade + Tailwind CSS 3.4 + Alpine.js 3.13 (sin React, sin Vue, sin Livewire)
- **Build:** Vite 5 con laravel-vite-plugin
- **BD:** MySQL 8 (charset utf8mb4, engine InnoDB, strict mode)
- **Testing:** Pest (no PHPUnit directo)
- **Code style:** Laravel Pint (preset laravel, strict_types obligatorio)
- **Deploy:** Docker multi-stage → Coolify (alternativa: nixpacks)
- **Dev local:** Docker Compose (app :8080, vite :5173, mysql :3307, mailpit :8025)

## Comandos frecuentes

```bash
# Dev
docker compose up -d
npm run dev                        # Vite HMR en :5173

# Tests
./vendor/bin/pest                  # Todos los tests (Unit + Feature)
./vendor/bin/pest --filter=NombreTest  # Test específico
npm run test:e2e                   # Tests de navegador (Playwright) — ver tests/e2e/README.md

# Code style
./vendor/bin/pint                  # Corregir estilo
./vendor/bin/pint --test           # Solo verificar

# Laravel
php artisan migrate
php artisan make:model Nombre -mfc  # Modelo + migración + factory + controller
php artisan make:controller NombreController
php artisan route:list

# Tareas en segundo plano (ver /admin/jobs)
php artisan queue:work                            # Sin esto no avanza ningún lote
php artisan jobs:run                              # Lista las tareas y sus pendientes
php artisan jobs:run portadas-libros --cantidad=200

# Seeders
php artisan db:seed --class=AdminUserSeeder       # Admin desde ADMIN_EMAIL / ADMIN_PASSWORD
php artisan db:seed --class=ProductionSeeder      # Catálogo real (5.246 productos)
php artisan db:seed --class=NonWorkingDaySeeder   # Festivos de fecha fija
php artisan db:seed --class=CatalogSeeder         # Catálogo de prueba
```

## Arquitectura y convenciones

- **Strict types** en todos los archivos PHP: `declare(strict_types=1);`
- Controladores invocables (`__invoke`) para acciones simples (ej. DashboardController)
- Form Requests para validación (ej. LoginRequest)
- Blade components reutilizables en `resources/views/components/`
- Layouts: `layouts/app.blade.php` (autenticado) y `layouts/guest.blade.php`
- Traducciones en `lang/es/`
- Rutas web en `routes/web.php`, API en `routes/api.php`
- **Datos de la tienda en `config/tienda.php`** — nombre, dirección, teléfono, email, coordenadas, horario y datos del titular (`legal.razon_social`, `legal.nif`, `legal.registro`, `legal.actualizado`). Fuente única: los usan el layout de la tienda (JSON-LD, metas geo, footer), la página de contacto, las páginas legales, el albarán en PDF y la firma de los emails. Nunca escribir estos datos a mano en una vista
- **El carrito nunca se inyecta en el constructor de un controlador**, siempre como parámetro del método. Laravel guarda la instancia del controlador dentro del objeto `Route`, así que una dependencia `scoped` inyectada en el constructor sobrevive a la petición que la creó
- Alpine.js stores globales: `$store.notifications` (toasts), `$store.ui` (dark mode, sidebar)
- Alpine.js components: `dropdown()`, `modal()`, `asyncForm()`
- Color de marca "brand" definido en `tailwind.config.js` (paleta **teal**, extraída del logo)
  - `brand-500: #00b5b5` — teal principal (cuerpo del barco)
  - `brand-900: #1a4e6a` — navy oscuro (mástil/sombra)
  - `brand-200: #a4eeee` — teal claro (reflejos)
- Logo: `public/assets/logo-barco-papel.png` — usar siempre `<img>` con esta ruta, nunca SVG genérico
- Dark mode por clase CSS (`darkMode: 'class'`)

## Estructura de directorios clave

```
app/
  Http/
    Controllers/
      Auth/               # Controladores de autenticación
      Admin/              # CRUD admin: Products, Orders, Categories, DeliveryZones,
                          # NonWorkingDays, Customers, NotificationLog, Verial, Jobs
    Requests/Admin/       # Form Requests del admin
  Models/
    Traits/HasAudit.php   # Trait de auditoría — añadir `use HasAudit` a cualquier modelo
    *.php                 # Category, Product, Order, OrderItem, DeliveryZone, NonWorkingDay,
                          # Post, User, NotificationLog, ProductImage, AuditLog
  Rules/                  # Reglas de validación (CodigoPostalConReparto)
  View/Components/Store/  # Componentes Blade de clase (CartBadge)
  Services/
    Books/                # Portadas y fichas por ISBN (BookEnricher, GoogleBooksQuota, CoverOutcome)
    Cart/                 # Carrito en sesión (Cart, CartItem)
    Checkout/             # PlaceOrderService: carrito → pedido
    Delivery/             # DeliveryZoneResolver (CP → zona y tarifa) y DeliveryCalendar (días de reparto)
    Jobs/                 # Tareas por lotes del panel /admin/jobs
      BatchTask.php               # Interface de tarea
      ResettableTask.php          # Interface opcional: reintentar lo descartado
      BatchTaskRegistry.php       # Registro (se rellena en AppServiceProvider)
      BatchHistory.php            # Lectura de job_batches
      Tasks/BookCoverTask.php     # Portadas de libros (implementada)
    Notifications/        # Sistema de notificaciones extensible por canales
      NotificationChannel.php       # Interface para canales
      OrderNotificationService.php  # Orquestador: send, sendAll, resend
      Channels/EmailChannel.php     # Canal email (implementado)
    Verial/               # Cliente e integración con el ERP (sync catálogo, stock, pedidos)
  Jobs/                   # FetchBookDataFromIsbn (portadas), Verial/ (import, stock, envío de
                          # pedidos) y Csv/ (cargas masivas)
  Console/Commands/       # verial:* , jobs:run, import:books-csv, books:reprocess
  Providers/              # AppServiceProvider (HTTPS forzado en prod, registro de canales y servicios scoped)
config/                   # Configuración Laravel
database/migrations/      # Migraciones de BD
resources/
  views/
    admin/                # Panel de administración
      categories/         # CRUD categorías
      products/           # CRUD productos
      orders/             # CRUD pedidos (con show)
      delivery-zones/     # CRUD zonas de reparto
      non-working-days/   # CRUD festivos y cierres
      customers/          # Ficha de cliente: pedidos y avisos enviados
      jobs/               # Panel de tareas en segundo plano
      verial/             # Panel del conector con el ERP
      dashboard.blade.php
    errors/               # Páginas de error personalizadas (403, 404, 500)
    auth/                 # Vistas de autenticación
    components/
      admin/              # Componentes del admin (nav-link, card, table, status-badge, audit-timeline, etc.)
      layouts/
        store.blade.php   # Layout tienda pública (<x-layouts.store>)
        guest.blade.php   # Layout auth/login (<x-layouts.guest>)
    layouts/
      admin.blade.php     # Layout admin con sidebar (@extends)
      app.blade.php       # Layout principal autenticado (@extends)
  css/app.css             # Entry point Tailwind
  js/app.js               # Entry point Alpine.js + stores
routes/
  web.php                 # Rutas web + admin (prefix /admin)
  api.php                 # Rutas API
tests/
  Feature/                # Tests de integración
  Unit/                   # Tests unitarios
```

## Admin Backend (/admin)

- **Layout:** sidebar fijo con navegación, topbar con título de página, flash messages
- **Diseño:** paleta white + brand-50/100 (teal clarito), bordes brand-100, rounded-2xl
- **Rutas:** prefijo `/admin`, name prefix `admin.`, middleware `auth` + `verified` + `admin`
- **Acceso:** solo usuarios con `is_admin = true` (middleware `EnsureUserIsAdmin`)
- **Modelos:**
  - `Category` — categorías de productos (name, slug, description, is_active, sort_order)
  - `Product` — productos (category_id, name, slug, sku, description, price, stock, image, is_active)
  - `Order` — pedidos manuales (order_number auto BP-YYYYMMDD-XXXXX, customer_*, delivery_*, status, totals)
  - `OrderItem` — líneas de pedido (product_id, quantity, unit_price, total)
  - `DeliveryZone` — zonas de reparto (postal_code, neighborhood, city, delivery_fee, delivery_days, is_active)
  - `NonWorkingDay` — festivos y cierres (name, starts_on, ends_on, recurs_annually)
- **Estados de pedido:** pendiente (amarillo), preparado (azul), en_reparto (morado), entregado (verde)
- **Componentes Blade admin:** `x-admin.nav-link`, `x-admin.card`, `x-admin.stat-card`, `x-admin.table`, `x-admin.th`, `x-admin.td`, `x-admin.status-badge`, `x-admin.empty-state`, `x-admin.audit-timeline`
- **PDF:** albarán de pedido con `barryvdh/laravel-dompdf` (ruta `GET /admin/orders/{order}/pdf`)
- **Dashboard:** gráficas con Chart.js (ventas 7 días, pedidos por estado, top productos)
- **Galería:** tabla `product_images`, upload múltiple, borrado selectivo en edición

## Carrito y checkout (tienda pública)

- **Carrito en sesión:** `App\Services\Cart\Cart` (registrado como `scoped`), clave de sesión `carrito` → `[product_id => cantidad]`. Solo guarda IDs y cantidades: los precios se releen de BD en cada lectura para que un cambio de tarifa se aplique antes de confirmar.
- **Reconciliación:** en cada lectura descarta productos borrados o despublicados, retira los agotados y recorta cantidades al stock real. Los avisos se exponen en `Cart::adjustments()` y se pintan en la vista del carrito.
- **Tope por línea:** `Cart::MAX_QUANTITY` (99), además del stock disponible.
- **Zonas de reparto:** `App\Services\Delivery\DeliveryZoneResolver` resuelve CP → zona. Si un CP tiene varios barrios de alta, **aplica la tarifa más baja**. Sin zona activa no hay venta posible (no hay paquetería externa).
- **Días de reparto:** cada zona tiene `delivery_days` (JSON con días ISO-8601, 1 = lunes … 7 = domingo), editable con checkboxes en el admin (`<x-admin.delivery-days />`). **Sin días marcados (`null`) se reparte cualquier día que la librería abra**, tomado de `config('tienda.horario')` — así el domingo queda fuera sin configurar nada. `DeliveryZone::nextDeliveryDate()` calcula el primer reparto posible **contando desde el día siguiente**: hace falta al menos una jornada de preparación, así que un pedido en jueves a una zona que reparte los jueves entra en el jueves siguiente. La etiqueta legible sale de `deliveryDaysLabel()` («jueves», «lunes y jueves», «de lunes a sábado», «todos los días»).
- **Días sin reparto:** tabla `non_working_days` (festivos y cierres por vacaciones), con CRUD en `/admin/non-working-days`. Cada fila es un rango (`starts_on`/`ends_on`; iguales si es un solo día) y puede marcarse `recurs_annually` para los festivos de fecha fija, que se comparan por mes-día y valen para todos los años (un cierre recurrente no puede cruzar el cambio de año). `NonWorkingDaySeeder` carga los festivos fijos nacionales, el de Baleares y Sant Ciriac: `php artisan db:seed --class=NonWorkingDaySeeder`.
- **`App\Services\Delivery\DeliveryCalendar`** (registrado como `scoped`) es quien cruza días de reparto y días sin reparto: `nextDeliveryDate()`, `isClosed()`, `closuresDelaying()` (para explicar al cliente qué festivo ha movido su entrega) y `upcomingClosures()` (para publicarlos en la página de reparto). Lee los cierres una sola vez por petición y los filtra en memoria, porque los recurrentes no se pueden comparar en SQL de forma portable entre MySQL y SQLite. `DeliveryZone::nextDeliveryDate()` delega aquí.
- **Fecha prevista de entrega:** `PlaceOrderService` la guarda en `orders.estimated_delivery_date` al confirmar, para que el checkout, la confirmación, el email, el albarán y el panel digan lo mismo. Se muestra con `Order::formattedEstimatedDelivery()`. El endpoint `/comprobar-codigo-postal` la devuelve en vivo (`proxima_entrega`, `dias_reparto`) y la pintan `postalChecker`/`checkoutForm`.
- **Confirmación del pedido:** `App\Services\Checkout\PlaceOrderService` — transacción con `lockForUpdate()` sobre los productos, descuento de stock, creación de `Order` + `OrderItem`, y `CheckoutException` (con rollback) si el stock no alcanza.
- **Validación:** `App\Http\Requests\Store\CheckoutRequest` + regla `App\Rules\CodigoPostalConReparto`.
- **Pago:** no hay pasarela. Se paga en el momento de la entrega.
- **Comprar exige cuenta con el correo verificado.** Las rutas `checkout.show` y `checkout.store` van bajo `auth` + `verified`; el carrito sigue siendo libre para cualquiera. Quien no ha entrado ve en el carrito la invitación a iniciar sesión o registrarse, y quien no ha confirmado el correo, el aviso para reenviarlo. El carrito sobrevive al login porque `session()->regenerate()` conserva los datos
- **`customer_email` es obligatorio** en el checkout; si el formulario llega sin él se toma el de la cuenta
- **Los pedidos sin `user_id` siguen existiendo:** los anteriores a exigir cuenta y los que da de alta el admin a mano, que puede seguir creándolos sin cliente registrado. La ficha de cliente los recupera por correo
- **Acceso a la confirmación:** por sesión —para que el cliente la vea aunque cierre sesión justo después—, por `user_id` y para admins. Un tercero recibe 403.
- **Rutas:** `/carrito` (index/add/update/remove/clear), `/comprobar-codigo-postal` (JSON), `/finalizar-pedido` (show/store, POST con `throttle:10,1`), `/pedido/{orderNumber}`.
- **Componentes Alpine:** `postalChecker(endpoint, cpInicial)` y `checkoutForm(...)` en `resources/js/app.js`.
- **Componente Blade:** `<x-store.cart-badge />` (contador en la cabecera, acepta `mobile`) y `<x-store.flash />` (mensajes flash de la tienda).
- **El pedido no cambia entre el resumen y la confirmación:** `CheckoutController::show` guarda en sesión (`checkout_resumen`) una `App\Services\Cart\CartSnapshot` con lo que el cliente está viendo —líneas, cantidades y precios—. Al confirmar se contrasta con el estado actual del carrito: si algo cambió (línea retirada, cantidad recortada por stock, precio actualizado desde el ERP) **no se crea el pedido**; se vuelve al formulario con `cart_changes` detallando el cambio para que el cliente lo acepte de nuevo. Sin resumen en sesión (POST directo) no hay nada que contrastar y el pedido sigue su curso.
- **Páginas legales:** `/aviso-legal`, `/privacidad` y `/condiciones-de-venta` (`LegalController`, vistas en `store/legal/`, envueltas en `<x-store.legal-page>`). Enlazadas desde el footer, el checkbox de condiciones del checkout y el sitemap. Los textos reflejan el funcionamiento real: reparto propio, pago contra entrega, sin pasarela.
- **IVA:** no se desglosa por producto (la factura la emite Verial). Las vistas de carrito, checkout y confirmación indican «IVA incluido», y el albarán en PDF advierte además de que no tiene la consideración de factura.
- **Pedidos y Verial:** el pedido web nace `pendiente` y **no** se envía al ERP en ese momento. Entra en Verial cuando el admin lo marca `preparado` (`Admin\OrderController::updateStatus`), que es el mismo umbral que usa `verial:send-pending-orders`. Así hay un único punto de disparo y no se duplican envíos.

## Integración con Verial (ERP)

- **Cliente:** `App\Services\Verial\VerialClient` (singleton), configurado en `config/verial.php` desde `VERIAL_HOST`, `VERIAL_PORT`, `VERIAL_SESSION`, `VERIAL_TARIFA` y `VERIAL_TIMEOUT`.
- **Sin configurar, la tienda funciona igual:** si falta `host` o `session` no se envía nada al ERP y el pedido queda con `verial_enviado_at` a null, listo para que lo recoja `verial:send-pending-orders` cuando el ERP vuelva. Los tests cubren este caso.
- **Servicios:** `SyncCatalogService`, `SyncStockService`, `SyncImagesService`, `SyncFamiliesService`, `SyncFabricantesService`, `SyncOrderStatusService`, `SendOrderService` y `RegisterClientService`. Todos devuelven un `SyncResult`.
- **Jobs:** `Verial\ImportProductJob`, `Verial\UpdateStockJob`, `Verial\SendOrderToVerialJob`; `Csv\ProcessStockCsvJob` y `Csv\ProcessPricesCsvJob` para las cargas por CSV.
- **Comandos y su hueco en el scheduler** (`routes/console.php`):
  - `verial:sync-stock` — cada hora entre las 09:00 y las 21:00
  - `verial:sync-catalog` — diario a las 02:00
  - `verial:send-pending-orders` — cada 5 minutos
  - `verial:sync-order-status` — cada 15 minutos
  - `verial:sync-images` — manual
- **Panel:** `/admin/verial` (`Admin\VerialSyncController`) para lanzar sincronizaciones y subir CSV a mano.
- **Campos en `orders`:** `verial_pedido_id`, `verial_referencia`, `verial_estado`, `verial_enviado_at`. En `order_items` y `products`, `verial_id`.
- Notas de diseño de la integración en `PLAN_CONECTOR.md`.

## Tareas en segundo plano (/admin/jobs)

- **Panel:** `/admin/jobs` (`Admin\JobController`, vista `admin/jobs/index`) — pendientes de cada tarea, lanzar un lote con la cantidad que se quiera, barra de progreso del lote en curso, cancelarlo e historial de los últimos lotes. Enlazado en el sidebar bajo «Herramientas», junto al conector Verial
- **Arquitectura:** interface `App\Services\Jobs\BatchTask` → tareas registradas en `AppServiceProvider` a través de `BatchTaskRegistry`. Mismo patrón que los canales de notificación: **añadir una tarea es implementar la interfaz y registrarla**, sin tocar el panel. Si además implementa `ResettableTask`, el panel ofrece el botón de reintentar lo descartado
- **Progreso:** `Bus::batch()` con el nombre del lote igual a la clave de la tarea. `App\Services\Jobs\BatchHistory` lee `job_batches` por ese nombre, así que no hace falta ninguna tabla propia
- **Ritmo:** el lote se encola escalonado (`->delay()` por bloques de `per_minute`), que es determinista y no gasta reintentos del job. El middleware `RateLimited('google-books')` queda como red de seguridad para los despachos desde CLI
- **Un lote a la vez por tarea:** ni el panel ni `jobs:run` lanzan otro mientras haya uno sin terminar, para no duplicar peticiones a la API
- **Desde consola:** `php artisan jobs:run` lista las tareas con sus pendientes; `php artisan jobs:run portadas-libros --cantidad=200` lanza el lote. Nada avanza sin un `queue:work` en marcha (el panel lo advierte)
- **No está en el scheduler a propósito:** consume cuota de una API externa, así que se lanza a mano

### Tarea «portadas-libros»

- **`App\Services\Jobs\Tasks\BookCoverTask`** + `App\Jobs\FetchBookDataFromIsbn` + `App\Services\Books\BookEnricher`
- **Dos fuentes:** Google Books por ISBN (portada y metadatos: título, subtítulo, autores, editorial, páginas, año) y **OpenLibrary como respaldo**, que no tiene clave ni cuota y solo se consulta cuando Google no trae portada
- **La portada se descarga al disco público** como `covers/{isbn}.jpg`, nunca se enlaza a `books.google.com`. Un libro cuya `products.image` sea todavía una URL remota cuenta como pendiente, para traerla a local. Al conseguir portada el producto se activa
- **El título solo se sustituye si Google lo da en español o catalán**; en otro idioma se respeta el del ERP
- **Ningún libro se procesa dos veces:** cada intento queda marcado en `product_book_details` (`cover_source`, `cover_fetched_at`, `cover_attempts`, `cover_attempted_at`). Con portada, archivado; sin ella, se suma un intento hasta `ProductBookDetail::MAX_COVER_ATTEMPTS` (3), y entonces pasa a **descartado**. Ni los archivados ni los descartados vuelven a la cola: los descartados solo con el botón «Reintentar los descartados», que pone el contador a cero
- **Cuota:** `App\Services\Books\GoogleBooksQuota` cuenta en caché las peticiones del día (`GOOGLE_BOOKS_DAILY_QUOTA`, 1.000 por defecto) y el panel no deja encolar más libros de los que quedan. Un 429 o la cuota agotada **no cuentan como intento**: el job se libera y vuelve cuando la API se renueva
- **Refresco de fichas:** `books:reprocess` despacha el job con `refresh: true`, que vuelve a pedir los metadatos aunque el libro ya tenga portada (y no la toca). Es lo que arregla los títulos en mayúsculas que llegaron del CSV

## Correo

- **Layout único:** `<x-mail.layout>` (`components/mail/layout.blade.php`). **Todo correo que salga de la aplicación lo usa**, incluidos los de autenticación. Estilos en línea y maquetación con tablas, porque los clientes de correo no aplican hojas de estilo. Auxiliares: `<x-mail.boton>` y `<x-mail.pedido-detalle>`
- **Vistas en `resources/views/emails/`:** `orders/created`, `orders/status`, `orders/store-copy`, `auth/verify`, `auth/reset-password`, `auth/welcome`, y `plain.blade.php` para la alternativa en texto
- **Los correos de pedido van en HTML y en texto plano.** El texto es además lo que se guarda en `notification_logs.body`, para que el historial se lea sin renderizar nada
- **Autenticación:** `VerifyEmailNotification`, `ResetPasswordNotification` y `WelcomeNotification` en `app/Notifications/`, enganchadas desde `User::sendEmailVerificationNotification()` / `sendPasswordResetNotification()`. Sustituyen a las plantillas genéricas de Laravel, que salían en inglés y sin marca
- **La bienvenida se manda al verificar el correo** (evento `Verified` → `SendWelcomeEmail`), no al registrarse, para no soltar dos correos a la vez
- **Copia a la librería:** al confirmarse un pedido, `OrderNotificationService::notifyStore()` avisa al buzón de `config('tienda.email')` con los datos de contacto y las líneas. Se registra como un envío más, con `event = store_copy`. Solo en pedido nuevo: los cambios de estado los provoca la propia tienda
- **Reply-To** a `config('tienda.email')` en todos los correos
- Un fallo de correo nunca tumba la operación: `PlaceOrderService` y `SendWelcomeEmail` capturan y registran en el log

## Sistema de notificaciones

- **Arquitectura:** interface `NotificationChannel` → canales pluggables registrados en `AppServiceProvider`
- **Canal activo:** `EmailChannel`. Para añadir WhatsApp/Telegram: crear clase que implemente `NotificationChannel` y registrar en el provider
- **Servicio:** `OrderNotificationService` — `sendAll()`, `send()`, `resend()`
- **Registro:** tabla `notification_logs` (order_id, **user_id**, channel, recipient, subject, body, status, error_message, event, metadata, sent_at). **`order_id` y `user_id` son opcionales**: los avisos de pedido llevan los dos (si el cliente está registrado) y los de cuenta solo `user_id`
- **Todo aviso queda registrado, venga de donde venga.** Los de pedido los registra `OrderNotificationService`; los de cuenta —verificación, contraseña, bienvenida— los recoge el listener `LogSentNotification` desde el evento `NotificationSent` de Laravel, sin tocar cada notificación. Los correos de pedido no pasan por ese listener (los envía `EmailChannel` directamente), así que no se duplican
- **Dónde se ven:** en el pedido (`admin/orders/show`, con reenvío) y en la ficha del cliente (`admin/customers/show`, componente `<x-admin.notification-list>`). La ficha reúne los avisos por `user_id` y los de sus pedidos, incluidos los que hizo como invitado con el mismo correo
- **Vista:** historial en show pedido, envío manual con selección de canal, reenvío con corrección de destinatario
- **Contacto editable:** email y teléfono del cliente se pueden corregir inline desde el detalle del pedido
- **Transporte:** SMTP de Gmail con contraseña de aplicación (la cuenta es personal, no Workspace). El `From` tiene que ser la propia cuenta o una dirección verificada en «Enviar correo como», o Gmail lo reescribe. Todo correo lleva `Reply-To` a `config('tienda.email')` para que las respuestas del cliente lleguen al buzón público. En desarrollo, Docker Compose lo manda todo a Mailpit
- **Comprobar la configuración:** `php artisan mail:test destinatario@example.com` (`SendTestMailCommand`) — imprime el transporte en uso y envía un correo de prueba sin tocar pedidos

## Auditoría (HasAudit)

- **Trait:** `App\Models\Traits\HasAudit` — añadir `use HasAudit;` a cualquier modelo
- **Tabla:** `audit_logs` (polimórfica: auditable_type/id, user_id, event, old_values JSON, new_values JSON, ip_address, user_agent)
- **Eventos:** created, updated, deleted — se registran automáticamente
- **Excluye por defecto:** password, remember_token, created_at, updated_at
- **Personalizar:** `protected array $auditExclude = [...]` o `$auditInclude = [...]` en el modelo
- **Componente:** `<x-admin.audit-timeline :logs="$model->auditLogs" />` — timeline visual con diff campo a campo
- **Aplicado a:** Order (visible en show pedido como "Historial de cambios")

## Reglas para generar código

- Escribir todo en español: vistas, mensajes flash, validaciones, comentarios si son necesarios
- No usar React, Vue ni Livewire — solo Blade + Alpine.js + Tailwind
- Usar Tailwind utilities directamente, no CSS custom salvo excepciones justificadas
- Modelos con `$fillable` explícito, nunca `$guarded = []`
- Migraciones con tipos explícitos y constraints (foreign keys, indexes)
- Tests con Pest, no PHPUnit puro
- Passwords: mínimo 8 chars, mixed case + uncompromised en producción
- Validar en Form Requests, no en controllers
- Rutas agrupadas por middleware, con nombres (`->name()`)

## Contexto de negocio

- Los productos tienen catálogo visible para todos
- La compra/checkout solo está disponible para códigos postales donde la librería hace reparto
- No hay integración con paquetería externa — el reparto es propio
- Validar código postal del cliente antes de permitir checkout
- Precios en EUR (euros), formato europeo: 1.234,56 €
- Ubicación: Ibiza (Eivissa), Islas Baleares, España
