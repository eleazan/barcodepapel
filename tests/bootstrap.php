<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test bootstrap: forzar APP_ENV=testing antes del arranque de Laravel
|--------------------------------------------------------------------------
|
| El docker-compose.yml define APP_ENV=local como variable de entorno real
| del contenedor. putenv() aquí garantiza que getenv('APP_ENV') devuelva
| 'testing', de forma que Laravel cargue .env.testing y que
| PreventRequestForgery::runningUnitTests() devuelva true en los tests.
|
*/

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

require __DIR__ . '/../vendor/autoload.php';
