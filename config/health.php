<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Health Check Token
    |--------------------------------------------------------------------------
    |
    | Token required to access the health check endpoint. Set this in your
    | environment file to prevent unauthorized access to health status.
    |
    */

    'token' => env('HEALTH_CHECK_TOKEN', null),

    /*
    |--------------------------------------------------------------------------
    | Health Check Components
    |--------------------------------------------------------------------------
    |
    | List of components to check during health check.
    |
    */

    'checks' => [
        'database' => true,
        'cache'    => true,
        'storage'  => true,
    ],

];
