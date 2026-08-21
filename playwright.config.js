// @ts-check
import { defineConfig, devices } from '@playwright/test';

/**
 * Tests de navegador del recorrido de compra.
 *
 * Requieren la aplicación sirviendo en E2E_BASE_URL. Ver tests/e2e/README.md.
 */
export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: false, // el carrito vive en sesión: los tests comparten estado del catálogo
    workers: 1,
    retries: 0,
    reporter: [['list']],
    use: {
        baseURL: process.env.E2E_BASE_URL ?? 'http://localhost:8099',
        locale: 'es-ES',
        timezoneId: 'Europe/Madrid',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
});
