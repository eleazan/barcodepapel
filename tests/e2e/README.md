# Tests de navegador (Playwright)

Cubren el recorrido de compra con JavaScript real: contador del carrito,
selector de cantidad, comprobador de código postal por `fetch` y el total
recalculado en vivo. Todo eso queda fuera del alcance de Pest, que no ejecuta
Alpine.

No se ejecutan con `./vendor/bin/pest` — `phpunit.xml` solo mira `tests/Unit`
y `tests/Feature`.

## Requisitos

La aplicación tiene que estar sirviendo, **con los assets compilados**: si
falta `public/build/manifest.json` las vistas revientan al renderizar `@vite`.

```bash
docker compose up -d
docker compose exec node npm run build      # genera public/build
docker compose exec app php artisan migrate --seed
```

Los tests dan por hechos los datos del `CatalogSeeder`: el producto
«El Principito» a 9,95 € y las zonas de reparto 07800 (gratis) y 07810 (3 €).

## Ejecutar

```bash
npm run test:e2e
```

Contra otra URL:

```bash
E2E_BASE_URL=http://localhost:8080 npm run test:e2e
```

## Sin Playwright instalado en el host

La imagen oficial ya trae los navegadores. Con la app en un contenedor de la
red `barcodepapel_e2e`:

```bash
docker run --rm --network barcodepapel_e2e \
  -v "$PWD/tests/e2e:/work/tests/e2e" \
  -v "$PWD/playwright.config.js:/work/playwright.config.js" \
  -e E2E_BASE_URL=http://barcodepapel_app:8080 \
  -w /work mcr.microsoft.com/playwright:v1.49.1-noble \
  sh -c "npm i -D @playwright/test@1.49.1 && npx playwright test"
```

En Git Bash (Windows) hay que prefijar `MSYS_NO_PATHCONV=1`, o la conversión
automática de rutas rompe los `-v` y `-w`.

## Notas

- `fullyParallel: false` y `workers: 1` a propósito: el carrito vive en sesión
  y los tests comparten el catálogo, así que el estado de stock se pisaría.
- Los fallos dejan captura y traza en `test-results/`. Para abrir una traza:
  `npx playwright show-trace test-results/<carpeta>/trace.zip`.
