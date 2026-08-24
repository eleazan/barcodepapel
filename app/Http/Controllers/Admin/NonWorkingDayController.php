<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NonWorkingDayRequest;
use App\Models\NonWorkingDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NonWorkingDayController extends Controller
{
    public function index(): View
    {
        $days = NonWorkingDay::query()
            ->orderBy('recurs_annually')
            ->orderBy('starts_on')
            ->paginate(20);

        return view('admin.non-working-days.index', compact('days'));
    }

    public function create(): View
    {
        return view('admin.non-working-days.create');
    }

    public function store(NonWorkingDayRequest $request): RedirectResponse
    {
        NonWorkingDay::create($request->validated());

        return redirect()
            ->route('admin.non-working-days.index')
            ->with('success', 'Día sin reparto añadido correctamente.');
    }

    public function edit(NonWorkingDay $nonWorkingDay): View
    {
        return view('admin.non-working-days.edit', ['day' => $nonWorkingDay]);
    }

    public function update(NonWorkingDayRequest $request, NonWorkingDay $nonWorkingDay): RedirectResponse
    {
        $nonWorkingDay->update($request->validated());

        return redirect()
            ->route('admin.non-working-days.index')
            ->with('success', 'Día sin reparto actualizado correctamente.');
    }

    public function destroy(NonWorkingDay $nonWorkingDay): RedirectResponse
    {
        $nonWorkingDay->delete();

        return redirect()
            ->route('admin.non-working-days.index')
            ->with('success', 'Día sin reparto eliminado correctamente.');
    }
}
