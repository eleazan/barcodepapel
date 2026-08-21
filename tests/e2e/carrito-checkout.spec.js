// @ts-check
import { expect, test } from '@playwright/test';

/**
 * Recorrido de compra de punta a punta, con JavaScript real en el navegador.
 *
 * Cubre lo que los tests de Pest no pueden ver: los componentes Alpine
 * (contador del carrito, selector de cantidad, comprobador de código postal
 * por fetch y el total recalculado en vivo).
 */

const PRODUCTO = 'El Principito';

test('el contador del carrito refleja las unidades añadidas', async ({ page }) => {
    await page.goto('/catalogo');

    // Sin nada en el carrito no hay contador.
    const carrito = page.getByRole('link', { name: /Mi carrito/ }).first();
    await expect(carrito).toBeVisible();
    await expect(carrito).toHaveAttribute('aria-label', /vac/i);

    await page.locator('form[action*="/carrito/"] button[type=submit]').first().click();

    await expect(page.locator('header').getByText('1', { exact: true }).first()).toBeVisible();
    await expect(page.getByRole('link', { name: /Mi carrito/ }).first())
        .toHaveAttribute('aria-label', /1 art/i);
});

test('el selector de cantidad de la ficha respeta el stock', async ({ page }) => {
    await page.goto('/catalogo?buscar=Principito');
    await page.getByRole('link', { name: new RegExp(PRODUCTO) }).first().click();

    const cantidad = page.locator('#cantidad');
    await expect(cantidad).toHaveValue('1');

    // El botón de restar está deshabilitado en el mínimo.
    const menos = page.getByRole('button', { name: 'Quitar una unidad' });
    const mas = page.getByRole('button', { name: /adir una unidad/ });
    await expect(menos).toBeDisabled();

    await mas.click();
    await mas.click();
    await expect(cantidad).toHaveValue('3');
    await expect(menos).toBeEnabled();

    await menos.click();
    await expect(cantidad).toHaveValue('2');

    await page.getByRole('button', { name: /adir al carrito/ }).click();

    await expect(page).toHaveURL(/\/catalogo\//);
    await expect(page.getByText(/a.adido al carrito/i)).toBeVisible();
});

test('el comprobador de código postal consulta el servidor y muestra la tarifa', async ({ page }) => {
    await page.goto('/catalogo');
    await page.locator('form[action*="/carrito/"] button[type=submit]').first().click();
    await page.goto('/carrito');

    const cp = page.locator('#cp-carrito');

    // Código postal fuera de la isla.
    await cp.fill('28001');
    await page.getByRole('button', { name: 'Calcular' }).click();
    await expect(page.getByText(/No repartimos en ese c.digo postal/i)).toBeVisible();

    // Código postal con tarifa conocida del seeder: 07810 → 3,00 €
    await cp.fill('07810');
    await page.getByRole('button', { name: 'Calcular' }).click();
    await expect(page.getByText(/Repartimos en tu zona/i)).toBeVisible();
    await expect(page.getByText('3,00 €', { exact: true })).toBeVisible();

    // Formato inválido: no llega a llamar al servidor.
    await cp.fill('078');
    await page.getByRole('button', { name: 'Calcular' }).click();
    await expect(page.getByText(/5 d.gitos/i)).toBeVisible();
});

test('los botones de cantidad del carrito envían el valor actualizado', async ({ page }) => {
    await page.goto('/catalogo');
    await page.locator('form[action*="/carrito/"] button[type=submit]').first().click();
    await page.goto('/carrito');

    // Este es el caso que rompía sin $nextTick: el submit salía con el valor viejo.
    await page.getByRole('button', { name: /adir una unidad/ }).first().click();

    await expect(page.getByText('Carrito actualizado')).toBeVisible();
    await expect(page.locator('input[name=quantity]').first()).toHaveValue('2');
});

test('el checkout recalcula el total en vivo al escribir el código postal', async ({ page }) => {
    await page.goto('/catalogo?buscar=Principito');
    await page.getByRole('link', { name: new RegExp(PRODUCTO) }).first().click();
    await page.getByRole('button', { name: /adir al carrito/ }).click();

    await page.goto('/finalizar-pedido');

    // Antes de indicar el código postal el reparto está sin determinar.
    await expect(page.getByText(/Seg.n c.digo postal/i)).toBeVisible();

    await page.locator('#postal_code').fill('07810');
    await page.locator('#delivery_address').click(); // provoca el blur

    // 9,95 € del producto + 3,00 € de reparto = 12,95 €
    await expect(page.getByText(/Repartimos aqu/i)).toBeVisible();
    await expect(page.getByText('12,95 €')).toBeVisible();
});

test('un pedido completo llega a la página de confirmación', async ({ page }) => {
    await page.goto('/catalogo?buscar=Principito');
    await page.getByRole('link', { name: new RegExp(PRODUCTO) }).first().click();
    await page.getByRole('button', { name: /adir al carrito/ }).click();

    await page.goto('/finalizar-pedido');

    await page.locator('#customer_name').fill('Marta Serra Ribas');
    await page.locator('#customer_phone').fill('971123456');
    await page.locator('#customer_email').fill('marta@example.com');
    await page.locator('#postal_code').fill('07810');
    await page.locator('#delivery_address').fill('Carrer de la Mar 12, 3r B. Jesús');
    await page.locator('#notes').fill('Llamar antes de subir');
    await page.locator('#acepta_condiciones').check();

    await page.getByRole('button', { name: 'Confirmar pedido' }).click();

    await expect(page).toHaveURL(/\/pedido\/BP-\d{8}-/);
    await expect(page.getByRole('heading', { name: /Pedido recibido/ })).toBeVisible();
    await expect(page.getByText(/^BP-\d{8}-/)).toBeVisible();
    await expect(page.getByText('Marta Serra Ribas')).toBeVisible();
    await expect(page.getByText('Llamar antes de subir')).toBeVisible();
    await expect(page.getByText('12,95 €')).toBeVisible();

    // El carrito queda vacío tras la compra.
    await page.goto('/carrito');
    await expect(page.getByRole('heading', { name: /Tu carrito est. vac/ })).toBeVisible();
});

test('el checkout bloquea un código postal sin reparto', async ({ page }) => {
    await page.goto('/catalogo');
    await page.locator('form[action*="/carrito/"] button[type=submit]').first().click();
    await page.goto('/finalizar-pedido');

    await page.locator('#customer_name').fill('Cliente Peninsular');
    await page.locator('#customer_phone').fill('600111222');
    await page.locator('#postal_code').fill('28001');
    await page.locator('#delivery_address').fill('Gran Vía 1, Madrid');
    await page.locator('#acepta_condiciones').check();

    await page.getByRole('button', { name: 'Confirmar pedido' }).click();

    await expect(page).toHaveURL(/\/finalizar-pedido/);
    await expect(page.getByText(/No hacemos reparto en ese c.digo postal/i).first()).toBeVisible();
});

test('sin aceptar las condiciones el navegador no deja enviar', async ({ page }) => {
    await page.goto('/catalogo');
    await page.locator('form[action*="/carrito/"] button[type=submit]').first().click();
    await page.goto('/finalizar-pedido');

    await page.locator('#customer_name').fill('Cliente Indeciso');
    await page.locator('#customer_phone').fill('971000111');
    await page.locator('#postal_code').fill('07800');
    await page.locator('#delivery_address').fill('Carrer de Dalt Vila 3');
    // No marcamos el checkbox: el campo es required.

    const boton = page.getByRole('button', { name: 'Confirmar pedido' });
    await boton.click();

    // Seguimos en el formulario y el botón NO se queda bloqueado:
    // este era el bug de poner "enviando = true" en el @click del botón.
    await expect(page).toHaveURL(/\/finalizar-pedido/);
    await expect(boton).toBeEnabled();

    // Al corregirlo, el pedido sale adelante.
    await page.locator('#acepta_condiciones').check();
    await boton.click();
    await expect(page).toHaveURL(/\/pedido\/BP-/);
});

test('vaciar el carrito pide confirmación y lo deja vacío', async ({ page }) => {
    await page.goto('/catalogo');
    await page.locator('form[action*="/carrito/"] button[type=submit]').first().click();
    await page.goto('/carrito');

    page.on('dialog', dialog => dialog.accept());
    await page.getByRole('button', { name: 'Vaciar carrito' }).click();

    await expect(page.getByRole('heading', { name: /Tu carrito est. vac/ })).toBeVisible();
});
