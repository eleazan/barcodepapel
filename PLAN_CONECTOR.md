# Plan de integración — Conector Web Verial

Documento técnico para integrar Barco de Papel con el sistema ERP Verial mediante su Servicio Web v1.8.5.

---

## 1. Qué es el conector

Verial expone una API REST-like (JSON sobre HTTP) en el puerto 8000. Cada petición incluye un parámetro `x` con el número de sesión (autenticación). Las operaciones principales que nos interesan son:

| Método Verial              | Uso                                      |
|---------------------------|------------------------------------------|
| `GetArticulosWS`          | Importar/actualizar catálogo de productos |
| `GetStockArticulosWS`     | Sincronizar stock                         |
| `GetImagenesArticulosWS`  | Importar imágenes de artículos            |
| `GetCondicionesTarifaWS`  | Obtener precios por tarifa                |
| `GetFamiliaArticulosWS`   | Importar familias (→ categorías)          |
| `GetFabricantesWS`        | Importar fabricantes/editoriales          |
| `NuevoClienteWS`          | Crear cliente en Verial al registrarse    |
| `NuevoDocClienteWS`       | Enviar pedido a Verial                    |
| `EstadoPedidosWS`         | Consultar estado de pedidos enviados      |

---

## 2. Campos actuales vs. campos necesarios

### 2.1 Tabla `products` — campos actuales

```
id, category_id, name, slug, sku, description, price, stock,
image, is_active, created_at, updated_at
```

### 2.2 Campos que hay que añadir a `products`

| Campo                  | Tipo                    | Origen Verial          | Notas                                      |
|-----------------------|-------------------------|------------------------|--------------------------------------------|
| `verial_id`           | `unsignedInteger` UNIQUE | `CodigoArticulo`       | Clave de sincronización — índice obligatorio|
| `barcode`             | `string(30)` nullable   | `CodigoBarras`         | EAN-13 / ISBN-13                           |
| `tipo_articulo`       | `tinyInteger` default 1 | `TipoArticulo`         | 1=normal, 2=libro                          |
| `iva_percent`         | `decimal(5,2)` default 4| `PorcentajeIVA`        | 4% libros, 21% otros                       |
| `fecha_disponibilidad`| `date` nullable         | `FechaDisponibilidad`  | Mostrar en ficha si futuro                 |
| `fecha_inicio_venta`  | `date` nullable         | `FechaInicioVenta`     | No vender antes de esta fecha              |
| `fecha_inactivo`      | `datetime` nullable     | `FechaInactivo`        | Si está informada, desactivar producto      |
| `nexo`                | `string(100)` nullable  | `Nexo`                 | Agrupa variantes (color, talla, etc.)       |
| `peso`                | `decimal(10,3)` nullable| `Peso`                 | En kg; útil si se añade envío externo      |
| `verial_fabricante_id`| `unsignedBigInteger` nullable FK | `CodigoFabricante` | Editorial/fabricante                  |
| `verial_synced_at`    | `timestamp` nullable    | —                      | Última sincronización exitosa              |

### 2.3 Tabla `orders` — campos que hay que añadir

| Campo               | Tipo                  | Notas                                     |
|--------------------|-----------------------|-------------------------------------------|
| `verial_pedido_id` | `unsignedInteger` nullable | ID devuelto por `NuevoDocClienteWS`  |
| `verial_referencia`| `string(50)` nullable | Referencia alfanumérica de Verial         |
| `verial_estado`    | `string(30)` nullable | Estado según Verial (distinto del local)  |
| `verial_enviado_at`| `timestamp` nullable  | Cuándo se envió a Verial                  |

### 2.4 Tabla `order_items` — campos que hay que añadir

| Campo             | Tipo                   | Notas                         |
|------------------|------------------------|-------------------------------|
| `verial_id`      | `unsignedInteger` nullable | `CodigoArticulo` en la línea  |

### 2.5 Tabla `users` — campos que hay que añadir

| Campo              | Tipo                   | Notas                              |
|-------------------|------------------------|------------------------------------|
| `verial_cliente_id`| `unsignedInteger` nullable | ID devuelto por `NuevoClienteWS` |

---

## 3. Nuevas tablas

### 3.1 `verial_fabricantes`

Editoriales y fabricantes importados de Verial. Los productos referencian esta tabla.

```
id, verial_id (unique), nombre, created_at, updated_at
```

### 3.2 `product_book_details`

Metadatos específicos de libros (solo cuando `tipo_articulo = 2`).

```
id
product_id (FK → products, unique)
isbn            string(20) nullable
subtitulo       string(255) nullable
autores         string(500) nullable   -- lista separada por comas desde Verial
editorial       string(255) nullable   -- desnormalizado desde fabricante
coleccion       string(255) nullable
paginas         unsignedSmallInteger nullable
edicion         string(50) nullable
anio_publicacion year nullable
created_at, updated_at
```

### 3.3 `verial_sync_log`

Registro de operaciones de sincronización.

```
id
entity_type     string(50)     -- 'producto', 'stock', 'pedido', 'cliente'
entity_id       unsignedBigInteger nullable  -- ID local afectado
operation       string(50)     -- 'import_catalog', 'update_stock', 'send_order', etc.
verial_method   string(100)    -- método Verial llamado
status          enum('ok','error')
verial_response json nullable
error_message   text nullable
created_at
```

---

## 4. Arquitectura del código

### 4.1 Configuración (`config/verial.php`)

```php
return [
    'host'       => env('VERIAL_HOST', '127.0.0.1'),
    'port'       => env('VERIAL_PORT', 8000),
    'session'    => env('VERIAL_SESSION'),  // número de sesión (x)
    'timeout'    => env('VERIAL_TIMEOUT', 30),
    'tarifa'     => env('VERIAL_TARIFA', 1),  // código de tarifa de precios
];
```

Variables `.env` necesarias: `VERIAL_HOST`, `VERIAL_PORT`, `VERIAL_SESSION`, `VERIAL_TARIFA`.

### 4.2 Cliente HTTP (`app/Services/Verial/VerialClient.php`)

```
VerialClient
  __construct(Http $http, array $config)
  get(string $method, array $params): array
  post(string $method, array $body): array
  -- maneja autenticación (x), timeout, logging de errores
```

### 4.3 Servicios de sincronización

```
app/Services/Verial/
  VerialClient.php
  SyncCatalogService.php       -- GetArticulosWS → products + product_book_details
  SyncStockService.php         -- GetStockArticulosWS → products.stock
  SyncImagesService.php        -- GetImagenesArticulosWS → product_images
  SyncFabricantesService.php   -- GetFabricantesWS → verial_fabricantes
  SyncFamiliesService.php      -- GetFamiliaArticulosWS → categories
  SendOrderService.php         -- NuevoDocClienteWS → orders.verial_*
  SyncOrderStatusService.php   -- EstadoPedidosWS → orders.verial_estado
  RegisterClientService.php    -- NuevoClienteWS → users.verial_cliente_id
```

### 4.4 Comandos Artisan

```
php artisan verial:sync-catalog   [--since=YYYY-MM-DD] [--full]
php artisan verial:sync-stock
php artisan verial:sync-images
php artisan verial:send-pending-orders
php artisan verial:sync-order-status
```

### 4.5 Jobs de cola (Queue)

Cada comando Artisan despacha jobs individuales por lote para no bloquear:

```
Jobs/Verial/
  ImportProductJob
  UpdateStockJob
  SendOrderToVerialJob
```

### 4.6 Scheduler (`routes/console.php`)

```
verial:sync-stock          → cada hora (horario comercial)
verial:sync-catalog        → cada noche a las 02:00
verial:send-pending-orders → cada 5 minutos
verial:sync-order-status   → cada 15 minutos
```

---

## 5. Fases de implementación

### Fase 1 — Infraestructura y BD (prerequisito)

1. Crear `config/verial.php`
2. Crear migraciones:
   - `add_verial_fields_to_products_table`
   - `add_verial_fields_to_orders_table`
   - `add_verial_fields_to_order_items_table`
   - `add_verial_cliente_id_to_users_table`
   - `create_verial_fabricantes_table`
   - `create_product_book_details_table`
   - `create_verial_sync_log_table`
3. Actualizar modelos: `Product`, `Order`, `OrderItem`, `User` con nuevos fillables
4. Crear modelos: `VerialFabricante`, `ProductBookDetail`, `VerialSyncLog`
5. Implementar `VerialClient` con tests unitarios (mock HTTP)

### Fase 2 — Sincronización de catálogo (núcleo)

1. `SyncFabricantesService` + comando + job
2. `SyncFamiliesService` (mapea familias Verial → categories)
3. `SyncCatalogService`:
   - Upsert por `verial_id`
   - Detectar y mapear `tipo_articulo`
   - Poblar `product_book_details` si es libro
   - Desactivar si `fecha_inactivo` está informada
4. `SyncImagesService` — descarga imágenes y guarda en `product_images`
5. Sincronización de precios con `GetCondicionesTarifaWS`
6. Comando `verial:sync-catalog --full` y `--since`
7. Tests de Feature para el flujo completo con respuestas fixtures

### Fase 3 — Sincronización de stock

1. `SyncStockService` — actualiza `products.stock` masivamente
2. Comando `verial:sync-stock`
3. Scheduler horario

### Fase 4 — Envío de pedidos a Verial

1. `SendOrderService`:
   - Convierte `Order` + `OrderItems` al formato `NuevoDocClienteWS`
   - Guarda `verial_pedido_id` y `verial_referencia` en la orden
   - Registra en `verial_sync_log`
2. `SendOrderToVerialJob` — se despacha al cambiar estado a `preparado`
3. Comando `verial:send-pending-orders` para reeintentos
4. UI admin: mostrar referencia Verial en detalle de pedido

### Fase 5 — Registro de clientes

1. `RegisterClientService` — llama a `NuevoClienteWS` al registrarse un usuario
2. Escucha evento `Registered` de Laravel
3. Guardar `verial_cliente_id` en `users`

### Fase 6 — Sincronización de estado de pedidos

1. `SyncOrderStatusService` — consulta `EstadoPedidosWS` y actualiza `verial_estado`
2. Scheduler cada 15 minutos
3. Posible webhook hacia el cliente cuando cambia estado en Verial

### Fase 7 — Observabilidad y admin

1. Vista admin `/admin/verial/sync` — logs recientes, estado de última sync, botón "Sincronizar ahora"
2. Alertas por email si una sync falla 3 veces seguidas
3. Indicador en la ficha de producto: "Sincronizado con Verial" + fecha

---

## 6. Consideraciones importantes

### Mapeo de categorías Verial → categorías locales
Verial usa `CodigoFamilia` (jerarquía de familias). Necesitamos una tabla de mapeo o una convención para importar automáticamente. Propuesta: añadir `verial_familia_id` a `categories` y crear familias como categorías si no existen.

### Precios con IVA
Verial devuelve `PrecioConIVA` y `PorcentajeIVA`. La web muestra precios con IVA incluido (obligatorio en B2C). Guardar el `iva_percent` en producto permite recalcular precios base si se necesita facturación.

### Imágenes
`GetImagenesArticulosWS` devuelve imágenes en base64 o URL según configuración. Las descargamos y guardamos en `storage/app/public/products/` usando el sistema de `product_images` ya existente.

### Variantes y nexo
El campo `nexo` agrupa variantes del mismo producto (ej. mismo libro en tapa dura/blanda). Por ahora no implementamos variantes en la tienda — se importa cada variante como producto independiente. Cuando se necesite, `nexo` permite agruparlas en una vista de producto con selector.

### Idempotencia
Todos los upserts usan `updateOrCreate(['verial_id' => $id], [...])`. El scheduler puede ejecutarse en paralelo sin riesgo de duplicados.

### Gestión de errores
- Timeout de red → reintento automático via Queue con backoff exponencial
- Artículo con datos inválidos → registrar en `verial_sync_log` con `status=error`, continuar con siguiente
- Sesión expirada → renovar llamando al método de autenticación inicial

---

## 7. Variables de entorno a añadir a `.env.example`

```env
# Verial Web Service
VERIAL_HOST=192.168.1.x
VERIAL_PORT=8000
VERIAL_SESSION=         # número de sesión (obtener del servidor Verial)
VERIAL_TARIFA=1         # código de tarifa de precios públicos
VERIAL_TIMEOUT=30
```

---

## 8. Orden de desarrollo recomendado

```
1. [ ] Fase 1 — Migraciones + modelos + VerialClient
2. [ ] Fase 2 — SyncCatalog (solo texto, sin imágenes)
3. [ ] Fase 3 — SyncStock
4. [ ] Fase 2b — SyncImages
5. [ ] Fase 4 — SendOrder
6. [ ] Fase 5 — RegisterClient
7. [ ] Fase 6 — SyncOrderStatus
8. [ ] Fase 7 — UI admin de observabilidad
```

Avanzar fase a fase validando contra el servidor Verial de prueba antes de activar el scheduler en producción.
