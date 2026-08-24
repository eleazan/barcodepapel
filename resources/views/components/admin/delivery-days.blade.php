@props([
    'selected' => [],
])

@php
    $seleccionados = array_map('intval', (array) $selected);
@endphp

<div>
    <span class="form-label">Días de reparto</span>
    <p class="text-xs text-gray-500 mb-3">
        Marca los días en que se reparte en esta zona (por ejemplo, solo los jueves en Santa Eulària).
        Si no marcas ninguno, se reparte cualquier día que la librería esté abierta.
    </p>

    <div class="flex flex-wrap gap-2">
        @foreach (\App\Models\DeliveryZone::DAYS as $numero => $nombre)
            <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-brand-100 bg-white hover:bg-brand-50 cursor-pointer transition-colors">
                <input
                    type="checkbox"
                    name="delivery_days[]"
                    value="{{ $numero }}"
                    class="rounded border-gray-300 text-sky-500 focus:ring-sky-500"
                    @checked(in_array($numero, $seleccionados, true))
                >
                <span class="text-sm text-gray-600 capitalize">{{ $nombre }}</span>
            </label>
        @endforeach
    </div>

    @error('delivery_days')
        <p class="form-error">{{ $message }}</p>
    @enderror
    @error('delivery_days.*')
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
