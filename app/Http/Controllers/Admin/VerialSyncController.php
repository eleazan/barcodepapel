<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadCsvRequest;
use App\Jobs\Csv\ProcessPricesCsvJob;
use App\Jobs\Csv\ProcessStockCsvJob;
use App\Models\Product;
use App\Models\VerialSyncLog;
use App\Services\Verial\VerialClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class VerialSyncController extends Controller
{
    public function __construct(
        private readonly VerialClient $client,
    ) {}

    public function index(): View
    {
        $isConfigured = $this->client->isConfigured();

        $totalSyncronizados = Product::whereNotNull('verial_id')->count();
        $totalSinSincronizar = Product::whereNull('verial_id')->count();

        $lastSync = Product::whereNotNull('verial_synced_at')
            ->max('verial_synced_at');

        $erroresUltimas24h = VerialSyncLog::where('status', 'error')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $logs = VerialSyncLog::orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('admin.verial.index', compact(
            'isConfigured',
            'totalSyncronizados',
            'totalSinSincronizar',
            'lastSync',
            'erroresUltimas24h',
            'logs',
        ));
    }

    public function syncCatalog(): RedirectResponse
    {
        Artisan::call('verial:sync-catalog');

        return redirect()->route('admin.verial.index')
            ->with('success', 'Sincronización de catálogo completada.');
    }

    public function syncCatalogIncremental(): RedirectResponse
    {
        Artisan::call('verial:sync-catalog', ['--since' => now()->toDateString()]);

        return redirect()->route('admin.verial.index')
            ->with('success', 'Sincronización incremental de catálogo completada.');
    }

    public function syncStock(): RedirectResponse
    {
        Artisan::call('verial:sync-stock');

        return redirect()->route('admin.verial.index')
            ->with('success', 'Stock actualizado desde Verial.');
    }

    public function syncImages(): RedirectResponse
    {
        Artisan::call('verial:sync-images');

        return redirect()->route('admin.verial.index')
            ->with('success', 'Imágenes sincronizadas desde Verial.');
    }

    public function sendPendingOrders(): RedirectResponse
    {
        Artisan::call('verial:send-pending-orders');

        return redirect()->route('admin.verial.index')
            ->with('success', 'Pedidos pendientes enviados a Verial.');
    }

    public function syncOrderStatus(): RedirectResponse
    {
        Artisan::call('verial:sync-order-status');

        return redirect()->route('admin.verial.index')
            ->with('success', 'Estado de pedidos actualizado desde Verial.');
    }

    public function uploadStockCsv(UploadCsvRequest $request): RedirectResponse
    {
        $file = $request->file('csv');
        $path = $file->store('csv-imports');

        ProcessStockCsvJob::dispatch($path, $file->getClientOriginalName());

        return redirect()->route('admin.verial.index')
            ->with('success', 'CSV de stock enviado a la cola. El stock se actualizará en breve.');
    }

    public function uploadPricesCsv(UploadCsvRequest $request): RedirectResponse
    {
        $file = $request->file('csv');
        $path = $file->store('csv-imports');

        ProcessPricesCsvJob::dispatch($path, $file->getClientOriginalName());

        return redirect()->route('admin.verial.index')
            ->with('success', 'CSV de precios enviado a la cola. Los precios se actualizarán en breve.');
    }
}
