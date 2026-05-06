<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/status', function () {
    return response()->json(['status' => 'ok']);
});
