# Barco de Papel

Tienda web de **Barco de Papel**, una librería de Ibiza (Eivissa). Muestra el
catálogo completo, acepta pedidos y los reparte la propia librería: no hay
paquetería externa ni pasarela de pago, se cobra en la entrega.

El catálogo y el stock se sincronizan con **Verial**, el ERP de la librería.

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 13, PHP 8.5+ |
| Frontend | Blade + Alpine.js 3 + Tailwind CSS 3 |
| Base de datos | MySQL 8 (SQLite en los tests) |
| Build | Vite 5 |
| Tests | Pest |
| Estilo | Laravel Pint (preset `laravel`, `strict_types` obligatorio) |
| Contenedor | Docker (PHP-FPM + Nginx + Supervisor) |
| Despliegue | Coolify |

---

## Qué hace

**Tienda pública**

- Catálogo con categorías, búsqueda, orden y ficha de producto con galería.
- Carrito en sesión que se reconcilia con el catálogo en cada lectura: retira
  lo agotado o despublicado y ajusta cantidades al stock real.
- Comprobador de código postal: solo se puede comprar donde la librería
  reparte.
- Checkout sin registro, con fecha de entrega calculada según los días de
  reparto de la zona y los festivos.
- Blog y páginas legales (aviso legal, privacidad, condiciones de venta).

**Panel de administración** (`/admin`, solo usuarios con `is_admin`)

- CRUD de productos, categorías, pedidos, zonas de reparto y días sin reparto.
- Albarán en PDF, historial de cambios por pedido (auditoría) y registro de
  notificaciones enviadas.
- Panel de sincronización con Verial y carga de stock/precios por CSV.

---

## Puesta en marcha con Docker

```bash
cp .env.example .env
docker compose up -d          # app :8080 · vite :5173 · mysql :3307 · mailpit :8025
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan storage:link
```

Datos iniciales:

```bash
# Usuario administrador — lee ADMIN_EMAIL y ADMIN_PASSWORD del .env
docker compose exec app php artisan db:seed --class=AdminUserSeeder

# Festivos de fecha fija (nacionales, Baleares y Sant Ciriac)
docker compose exec app php artisan db:seed --class=NonWorkingDaySeeder

# Catálogo real (5.246 productos) o catálogo de prueba
docker compose exec app php artisan db:seed --class=ProductionSeeder
docker compose exec app php artisan db:seed --class=CatalogSeeder
```

La tienda queda en `http://localhost:8080` y el correo saliente en Mailpit,
`http://localhost:8025`.

---

## Desarrollo

```bash
npm run dev            # Vite con HMR

./vendor/bin/pest      # Toda la suite
./vendor/bin/pest --filter=CheckoutTest
npm run test:e2e       # Navegador (Playwright) — ver tests/e2e/README.md

./vendor/bin/pint      # Corregir estilo
./vendor/bin/pint --test
```

Las convenciones de código están en **[AGENTS.md](AGENTS.md)**, que es también
lo que leen los agentes de IA (`CLAUDE.md` solo lo importa).

---

## Configuración

Además de las variables habituales de Laravel:

```env
APP_LOCALE=es
APP_TIMEZONE=Europe/Madrid

# Administrador que crea AdminUserSeeder
ADMIN_EMAIL=admin@barcodepapel.es
ADMIN_PASSWORD=

# Verial (ERP). Sin host o sesión, la tienda funciona igual: no se envía
# nada al ERP y los pedidos quedan a la espera de que vuelva.
VERIAL_HOST=
VERIAL_PORT=8000
VERIAL_SESSION=
VERIAL_TARIFA=1
VERIAL_TIMEOUT=30
```

Los datos de la librería —dirección, teléfono, email, horario, coordenadas y
datos fiscales del titular— viven en `config/tienda.php`, no en variables de
entorno. Son la fuente única para el JSON-LD, el footer, la página de contacto,
las páginas legales, el albarán y la firma de los correos.

---

## Sincronización con Verial

Tareas programadas (`routes/console.php`, requieren `schedule:run` cada minuto):

| Comando | Frecuencia |
|---------|-----------|
| `verial:sync-stock` | Cada hora, de 09:00 a 21:00 |
| `verial:sync-catalog` | Diario a las 02:00 |
| `verial:send-pending-orders` | Cada 5 minutos |
| `verial:sync-order-status` | Cada 15 minutos |
| `verial:sync-images` | Manual |

Un pedido web nace en estado `pendiente` y **no** se envía al ERP en ese
momento: entra en Verial cuando el administrador lo marca como `preparado`.

---

## Despliegue en Coolify

1. Nueva aplicación con build pack **Dockerfile**, puerto `80`.
2. Variables de entorno: las de arriba más `APP_ENV=production`,
   `APP_DEBUG=false`, `APP_URL`, credenciales de base de datos, correo SMTP y
   `HEALTH_CHECK_TOKEN`. Genera la clave con `php artisan key:generate --show`.
3. Health check en `/up`.
4. Al desplegar, el entrypoint espera a la base de datos, ejecuta
   `migrate --force`, cachea configuración/rutas/vistas y arranca PHP-FPM,
   Nginx, dos workers de cola y el scheduler vía Supervisor.

> La configuración **no** se cachea durante el build, solo en el arranque del
> contenedor: en el build todavía no existen las variables de entorno.

---

## Antes de abrir la tienda al público

- [ ] Rellenar `legal.razon_social` y `legal.nif` en `config/tienda.php` — son
      obligatorios por la LSSI y las páginas legales los omiten mientras estén
      vacíos.
- [ ] Revisar la fecha de `legal.actualizado` si se tocan los textos legales.
- [ ] Dar de alta las zonas de reparto con su tarifa y sus días.
- [ ] Cargar los festivos (`NonWorkingDaySeeder`) y añadir los móviles del año
      —Jueves y Viernes Santo, segunda fiesta de Pascua— y los cierres por
      vacaciones desde el panel.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`.
- [ ] Proveedor de correo real, no `log`.
- [ ] Copias de seguridad de la base de datos.

---

## Licencia

MIT
