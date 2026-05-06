<?php

declare(strict_types=1);

return [
    'host'    => env('VERIAL_HOST'),
    'port'    => (int) env('VERIAL_PORT', 8000),
    'session' => env('VERIAL_SESSION'),
    'timeout' => (int) env('VERIAL_TIMEOUT', 30),
    'tarifa'  => (int) env('VERIAL_TARIFA', 1),
];
