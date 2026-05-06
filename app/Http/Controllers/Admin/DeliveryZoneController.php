<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryZoneRequest;
use App\Models\DeliveryZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryZoneController extends Controller
{
    public function index(Request $request): View
    {
        $zones = DeliveryZone::query()
            ->when($request->search, fn ($q, $s) => $q->where('postal_code', 'like', "%{$s}%")
                ->orWhere('neighborhood', 'like', "%{$s}%"))
            ->orderBy('postal_code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.delivery-zones.index', compact('zones'));
    }

    public function create(): View
    {
        return view('admin.delivery-zones.create');
    }

    public function store(DeliveryZoneRequest $request): RedirectResponse
    {
        DeliveryZone::create($request->validated());

        return redirect()
            ->route('admin.delivery-zones.index')
            ->with('success', 'Zona de reparto creada correctamente.');
    }

    public function edit(DeliveryZone $deliveryZone): View
    {
        return view('admin.delivery-zones.edit', ['zone' => $deliveryZone]);
    }

    public function update(DeliveryZoneRequest $request, DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->update($request->validated());

        return redirect()
            ->route('admin.delivery-zones.index')
            ->with('success', 'Zona de reparto actualizada correctamente.');
    }

    public function destroy(DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->delete();

        return redirect()
            ->route('admin.delivery-zones.index')
            ->with('success', 'Zona de reparto eliminada correctamente.');
    }
}
