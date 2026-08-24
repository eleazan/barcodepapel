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
| Contenedor | Docker (`serversideup/php`: PHP-FPM + Nginx + s6-overlay) |
| Despliegue | Coolify |

---

## Qué hace

**Tienda pública**

- Catálogo con categorías, búsqueda, orden y ficha de producto con galería.
- Carrito en sesión que se reconcilia con el catálogo en cada lectura: retira
  lo agotado o despublicado y ajusta cantidades al stock real.
- Comprobador de código postal: solo se puede comprar donde la librería
  reparte.
- Checkout con cuenta obligatoria —el carrito es libre, pero para finalizar hay
  que iniciar sesión con el correo confirmado—, con fecha de entrega calculada
  según los días de reparto de la zona y los festivos.
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

El compose levanta además un contenedor `worker` (cola) y otro `scheduler`
(tareas programadas). Van aparte del contenedor de la aplicación porque el
código está montado por volumen y un worker en marcha no recoge los cambios:
después de tocar un job hay que reiniciarlo.

```bash
docker compose restart worker
docker compose logs -f worker
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

## Correo

En desarrollo no hay que configurar nada: Docker Compose manda todo el correo
a Mailpit, `http://localhost:8025`.

En producción la tienda envía por **SMTP de Gmail con una contraseña de
aplicación**:

1. Activa la verificación en dos pasos en la cuenta de Google.
2. Genera una contraseña de aplicación en
   [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords).
   Son 16 caracteres; se pegan **sin espacios**.
3. Rellena las variables:

   ```env
   MAIL_MAILER=smtp
   MAIL_SCHEME=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=la-cuenta@gmail.com
   MAIL_PASSWORD=la-contraseña-de-aplicación
   MAIL_FROM_ADDRESS=la-cuenta@gmail.com
   MAIL_FROM_NAME="Barco de Papel"
   ```

4. Comprueba que funciona sin tener que hacer un pedido:

   ```bash
   php artisan mail:test tu-correo@example.com
   ```

**Sobre el remitente.** Gmail solo deja enviar desde la propia cuenta o desde
una dirección verificada en *Configuración → Cuentas → «Enviar correo como»*.
Si pones un `MAIL_FROM_ADDRESS` que no sea una de ellas, Gmail lo reescribe y
el cliente ve un «enviado en nombre de». Las respuestas van siempre al
`tienda.email` de `config/tienda.php`, que se envía como `Reply-To`.

**Límites.** Una cuenta personal de Gmail admite unos 500 correos al día. De
momento sobra, pero si el volumen crece —o si hacen falta registros de entrega
y rebotes— toca pasar a un proveedor transaccional (Resend, Brevo, Postmark,
SES) con el dominio verificado. Solo cambian las variables de entorno.

---

## Sincronización con Verial

Tareas programadas (`routes/console.php`, las ejecuta el servicio `scheduler`
descrito en [Colas y tareas programadas](#colas-y-tareas-programadas)):

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

## Colas y tareas programadas

Hay trabajo que no ocurre durante la petición y necesita un worker: los lotes de
`/admin/jobs`, las cargas de stock y precios por CSV y —lo más importante— el
**envío de pedidos a Verial**, que se encola al marcar un pedido como
`preparado`. Sin worker esos pedidos se quedan en la tabla `jobs` y no llegan
nunca al ERP.

En producción no hace falta un recurso aparte para eso: el contenedor de la
aplicación arranca por **s6-overlay**, y a los servicios de la imagen base
(Nginx y PHP-FPM) se suman dos propios, definidos en `docker/s6/`:

| Servicio | Proceso | Qué cubre |
|----------|---------|-----------|
| `queue-worker` | `queue:work` | Toda la cola `default` |
| `scheduler` | `schedule:work` | Las tareas de `routes/console.php` |

Los supervisa s6 igual que a Nginx: arrancan con el contenedor y se relanzan si
se caen. El worker se recicla cada hora (`--max-time`) para no acumular fugas de
memoria ni quedarse con código viejo tras un despliegue.

**No se configuran como comando de post-despliegue.** El post-deploy de Coolify
se ejecuta una vez y el despliegue espera a que termine, así que un proceso que
no acaba nunca lo bloquearía y moriría con él.

Se afinan por variables de entorno, sin reconstruir la imagen —
`QUEUE_WORKER_TRIES`, `QUEUE_WORKER_TIMEOUT`, `QUEUE_WORKER_MAX_TIME`,
`QUEUE_WORKER_QUEUES`—; están documentadas en `.env.example`. Si algún día el
tráfico justifica mover el worker a un recurso propio, basta
`QUEUE_WORKER_ENABLED=false` (o `SCHEDULER_ENABLED=false`) para apagar el de
dentro. Todos los jobs usan la cola `default`: si un lote largo de portadas
llegara a retrasar el envío de un pedido, la salida es separar esa cola y
arrancar un segundo worker con `--queue=`.

Para ver qué está haciendo:

```bash
docker compose logs -f worker            # en local
# en producción, desde el terminal del contenedor en Coolify:
s6-rc -a list                            # servicios activos
php artisan queue:failed                 # jobs que han fallado
```

---

## Despliegue en Coolify

1. Nueva aplicación con build pack **Dockerfile**, puerto `8080` (es el que
   expone la imagen y el que escucha Nginx).
2. Variables de entorno: las de arriba más `APP_ENV=production`,
   `APP_DEBUG=false`, `APP_URL`, credenciales de base de datos, correo SMTP y
   `HEALTH_CHECK_TOKEN`. Genera la clave con `php artisan key:generate --show`.
3. **`AUTORUN_ENABLED=true`** — sin ella la imagen no migra ni cachea la
   configuración en el arranque, porque viene desactivada de fábrica.
4. Health check en `/up`.
5. Al desplegar, el entrypoint espera a la base de datos, ejecuta
   `migrate --force` y cachea la configuración; después s6 arranca PHP-FPM,
   Nginx, el worker de cola y el planificador.

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
