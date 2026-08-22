# CLAUDE.md — Barco de Papel

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
      Admin/              # CRUD admin: Products, Orders, Categories, DeliveryZones, NotificationLog
    Requests/Admin/       # Form Requests del admin
  Models/
    Traits/HasAudit.php   # Trait de auditoría — añadir `use HasAudit` a cualquier modelo
    *.php                 # Category, Product, Order, OrderItem, DeliveryZone, User, NotificationLog, ProductImage, AuditLog
  Rules/                  # Reglas de validación (CodigoPostalConReparto)
  View/Components/Store/  # Componentes Blade de clase (CartBadge)
  Services/
    Cart/                 # Carrito en sesión (Cart, CartItem)
    Checkout/             # PlaceOrderService: carrito → pedido
    Delivery/             # DeliveryZoneResolver: CP → zona y tarifa
    Notifications/        # Sistema de notificaciones extensible por canales
      NotificationChannel.php       # Interface para canales
      OrderNotificationService.php  # Orquestador: send, sendAll, resend
      Channels/EmailChannel.php     # Canal email (implementado)
  Providers/              # AppServiceProvider (HTTPS forzado en prod, registro de canales)
config/                   # Configuración Laravel
database/migrations/      # Migraciones de BD
resources/
  views/
    admin/                # Panel de administración
      categories/         # CRUD categorías
      products/           # CRUD productos
      orders/             # CRUD pedidos (con show)
      delivery-zones/     # CRUD zonas de reparto
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
  - `DeliveryZone` — zonas de reparto (postal_code, neighborhood, city, delivery_fee, is_active)
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
- **Confirmación del pedido:** `App\Services\Checkout\PlaceOrderService` — transacción con `lockForUpdate()` sobre los productos, descuento de stock, creación de `Order` + `OrderItem`, y `CheckoutException` (con rollback) si el stock no alcanza.
- **Validación:** `App\Http\Requests\Store\CheckoutRequest` + regla `App\Rules\CodigoPostalConReparto`.
- **Pago:** no hay pasarela. Se paga en el momento de la entrega.
- **Acceso a la confirmación:** por sesión para quien acaba de comprar (sin cuenta), por `user_id` para clientes registrados, y para admins. Un tercero recibe 403.
- **Rutas:** `/carrito` (index/add/update/remove/clear), `/comprobar-codigo-postal` (JSON), `/finalizar-pedido` (show/store, POST con `throttle:10,1`), `/pedido/{orderNumber}`.
- **Componentes Alpine:** `postalChecker(endpoint, cpInicial)` y `checkoutForm(...)` en `resources/js/app.js`.
- **Componente Blade:** `<x-store.cart-badge />` (contador en la cabecera, acepta `mobile`) y `<x-store.flash />` (mensajes flash de la tienda).
- **El pedido no cambia entre el resumen y la confirmación:** `CheckoutController::show` guarda en sesión (`checkout_resumen`) una `App\Services\Cart\CartSnapshot` con lo que el cliente está viendo —líneas, cantidades y precios—. Al confirmar se contrasta con el estado actual del carrito: si algo cambió (línea retirada, cantidad recortada por stock, precio actualizado desde el ERP) **no se crea el pedido**; se vuelve al formulario con `cart_changes` detallando el cambio para que el cliente lo acepte de nuevo. Sin resumen en sesión (POST directo) no hay nada que contrastar y el pedido sigue su curso.
- **Páginas legales:** `/aviso-legal`, `/privacidad` y `/condiciones-de-venta` (`LegalController`, vistas en `store/legal/`, envueltas en `<x-store.legal-page>`). Enlazadas desde el footer, el checkbox de condiciones del checkout y el sitemap. Los textos reflejan el funcionamiento real: reparto propio, pago contra entrega, sin pasarela.
- **IVA:** no se desglosa por producto (la factura la emite Verial). Las vistas de carrito, checkout y confirmación indican «IVA incluido», y el albarán en PDF advierte además de que no tiene la consideración de factura.
- **Pedidos y Verial:** el pedido web nace `pendiente` y **no** se envía al ERP en ese momento. Entra en Verial cuando el admin lo marca `preparado` (`Admin\OrderController::updateStatus`), que es el mismo umbral que usa `verial:send-pending-orders`. Así hay un único punto de disparo y no se duplican envíos.

## Sistema de notificaciones

- **Arquitectura:** interface `NotificationChannel` → canales pluggables registrados en `AppServiceProvider`
- **Canal activo:** `EmailChannel`. Para añadir WhatsApp/Telegram: crear clase que implemente `NotificationChannel` y registrar en el provider
- **Servicio:** `OrderNotificationService` — `sendAll()`, `send()`, `resend()`
- **Registro:** tabla `notification_logs` (order_id, channel, recipient, subject, body, status, error_message, event, metadata, sent_at)
- **Vista:** historial en show pedido, envío manual con selección de canal, reenvío con corrección de destinatario
- **Contacto editable:** email y teléfono del cliente se pueden corregir inline desde el detalle del pedido

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
