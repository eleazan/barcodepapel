<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
*/

Schedule::command('queue:prune-failed')->daily();
Schedule::command('cache:prune-stale-tags')->hourly();

/*
|--------------------------------------------------------------------------
| Verial sync
|--------------------------------------------------------------------------
*/

Schedule::command('verial:sync-stock')->hourlyAt(0)->between('09:00', '21:00');
Schedule::command('verial:sync-catalog')->dailyAt('02:00');
Schedule::command('verial:send-pending-orders')->everyFiveMinutes();
Schedule::command('verial:sync-order-status')->everyFifteenMinutes();
