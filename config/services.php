<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Servicios externos
    |--------------------------------------------------------------------------
    */

    'google_books' => [
        'key' => env('GOOGLE_BOOKS_API_KEY'),

        // Cuota diaria del proyecto en Google Cloud. Por defecto son 1.000
        // peticiones al día; el panel de tareas la muestra y no deja encolar
        // más libros de los que quedan por consumir.
        'daily_quota' => (int) env('GOOGLE_BOOKS_DAILY_QUOTA', 1000),

        // Ritmo al que se atacan las peticiones. Google admite 100 por cada
        // 100 s, así que 60/min deja margen.
        'per_minute' => (int) env('GOOGLE_BOOKS_PER_MINUTE', 60),
    ],

];
