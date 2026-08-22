<?php

declare(strict_types=1);

use App\Services\Cart\CartSnapshot;

/**
 * Fotografía de dos líneas, en el formato que se guarda en sesión.
 */
function resumen(array $lines): CartSnapshot
{
    return new CartSnapshot($lines);
}

it('no encuentra diferencias contra sí misma', function () {
    $snapshot = resumen([
        7 => ['nombre' => 'El Quijote', 'cantidad' => 2, 'precio' => 19.90],
    ]);

    expect($snapshot->differences($snapshot))->toBe([]);
});

it('ignora las diferencias de precio por debajo del céntimo', function () {
    $antes = resumen([7 => ['nombre' => 'El Quijote', 'cantidad' => 1, 'precio' => 19.90]]);
    $ahora = resumen([7 => ['nombre' => 'El Quijote', 'cantidad' => 1, 'precio' => 19.9000001]]);

    expect($antes->differences($ahora))->toBe([]);
});

it('detecta el producto añadido desde otra pestaña', function () {
    $antes = resumen([7 => ['nombre' => 'El Quijote', 'cantidad' => 1, 'precio' => 19.90]]);
    $ahora = resumen([
        7 => ['nombre' => 'El Quijote', 'cantidad' => 1, 'precio' => 19.90],
        9 => ['nombre' => 'Cuaderno A4', 'cantidad' => 1, 'precio' => 3.50],
    ]);

    expect($antes->differences($ahora))->toBe(['«Cuaderno A4» se ha añadido a tu pedido.']);
});

it('acumula varios cambios sobre la misma línea', function () {
    $antes = resumen([7 => ['nombre' => 'El Quijote', 'cantidad' => 3, 'precio' => 19.90]]);
    $ahora = resumen([7 => ['nombre' => 'El Quijote', 'cantidad' => 1, 'precio' => 21.00]]);

    expect($antes->differences($ahora))->toHaveCount(2);
});

it('reconstruye una fotografía guardada en sesión', function () {
    $snapshot = CartSnapshot::fromArray([
        '7' => ['nombre' => 'El Quijote', 'cantidad' => '2', 'precio' => '19.90'],
    ]);

    expect($snapshot)->not->toBeNull();
    expect($snapshot->lines)->toBe([
        7 => ['nombre' => 'El Quijote', 'cantidad' => 2, 'precio' => 19.90],
    ]);
});

it('descarta una fotografía con una forma que no reconoce', function (mixed $raw) {
    expect(CartSnapshot::fromArray($raw))->toBeNull();
})->with([
    'no es un array'       => ['cualquier cosa'],
    'línea incompleta'     => [[7 => ['nombre' => 'El Quijote']]],
    'línea no es array'    => [[7 => 'El Quijote']],
    'cantidad no numérica' => [[7 => ['nombre' => 'El Quijote', 'cantidad' => 'dos', 'precio' => 19.90]]],
    'clave no numérica'    => [['siete' => ['nombre' => 'El Quijote', 'cantidad' => 2, 'precio' => 19.90]]],
]);
