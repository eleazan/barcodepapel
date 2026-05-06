<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Health check endpoint for load balancers and monitoring.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $checks  = [];
        $healthy = true;

        // Database check
        try {
            DB::connection()->getPdo();
            $checks['database'] = ['status' => 'ok'];
        } catch (\Throwable $e) {
            $checks['database'] = ['status' => 'error', 'message' => 'Cannot connect to database'];
            $healthy = false;
        }

        // Cache check
        try {
            Cache::put('health_check', true, 10);
            $checks['cache'] = Cache::get('health_check') === true
                ? ['status' => 'ok']
                : ['status' => 'error', 'message' => 'Cache read/write failed'];
        } catch (\Throwable $e) {
            $checks['cache'] = ['status' => 'error', 'message' => 'Cache unavailable'];
            $healthy = false;
        }

        // Storage check
        try {
            $path = storage_path('framework/cache/.health');
            file_put_contents($path, time());
            unlink($path);
            $checks['storage'] = ['status' => 'ok'];
        } catch (\Throwable $e) {
            $checks['storage'] = ['status' => 'error', 'message' => 'Storage not writable'];
            $healthy = false;
        }

        return response()->json([
            'status'    => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks'    => $checks,
            'version'   => config('app.version', '1.0.0'),
        ], $healthy ? 200 : 503);
    }
}
