# ─────────────────────────────────────────────────────────────────────────────
# Stage 1: Node — compile frontend assets (production only)
# ─────────────────────────────────────────────────────────────────────────────
FROM node:20-alpine AS node-build

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --frozen-lockfile

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/css ./resources/css
COPY resources/js  ./resources/js
COPY resources/views ./resources/views

RUN npm run build

# ─────────────────────────────────────────────────────────────────────────────
# Stage 2: Development
# serversideup/php already includes: pdo_mysql, pdo_pgsql, redis, gd, zip,
# intl, bcmath, opcache, mbstring, pcntl, exif + Nginx + PHP-FPM
# ─────────────────────────────────────────────────────────────────────────────
FROM serversideup/php:8.5-fpm-nginx AS development

WORKDIR /var/www/html

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

COPY --chown=www-data:www-data . .
RUN composer dump-autoload

EXPOSE 80

# ─────────────────────────────────────────────────────────────────────────────
# Stage 3: Production
# ─────────────────────────────────────────────────────────────────────────────
FROM serversideup/php:8.5-fpm-nginx AS production

LABEL maintainer="BarcodePapel <dev@barcodepapel.com>"
LABEL org.opencontainers.image.title="BarcodePapel"
LABEL org.opencontainers.image.description="Laravel 13 Application"

WORKDIR /var/www/html

# Install PHP deps (no dev, optimized)
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

COPY --chown=www-data:www-data . .

# Copy compiled frontend assets from node-build
COPY --from=node-build --chown=www-data:www-data /app/public/build ./public/build

# Sin config:cache: en build las variables de entorno reales todavía no
# existen y quedarían congeladas en el caché (APP_ENV=local, DB vacía...).
# La imagen ya lo hace en cada arranque vía AUTORUN_LARAVEL_CONFIG_CACHE.
RUN composer dump-autoload --optimize --no-dev \
    && php artisan route:cache \
    && php artisan view:cache

# ─── Worker de cola y planificador como servicios de s6 ──────────────────────
# La imagen base arranca por s6-overlay (/init) solo Nginx y PHP-FPM. Estos dos
# servicios se suman ahí, así que los supervisa s6 igual que a Nginx: arrancan
# con el contenedor y se relevantan si se caen. Sin ellos ningún job se procesa
# (el envío de pedidos a Verial se queda en la tabla `jobs`) y ninguna tarea de
# routes/console.php se ejecuta.
USER root

COPY docker/s6/queue-worker /etc/s6-overlay/s6-rc.d/queue-worker
COPY docker/s6/scheduler    /etc/s6-overlay/s6-rc.d/scheduler

# El fichero vacío en user/contents.d es lo que da de alta el servicio.
RUN chmod +x /etc/s6-overlay/s6-rc.d/queue-worker/run \
             /etc/s6-overlay/s6-rc.d/queue-worker/finish \
             /etc/s6-overlay/s6-rc.d/scheduler/run \
             /etc/s6-overlay/s6-rc.d/scheduler/finish \
    && touch /etc/s6-overlay/s6-rc.d/user/contents.d/queue-worker \
             /etc/s6-overlay/s6-rc.d/user/contents.d/scheduler

USER www-data

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD curl -f http://localhost:8080/up || exit 1
