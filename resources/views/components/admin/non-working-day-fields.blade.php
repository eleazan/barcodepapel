@props([
    'day' => null,
])

<div>
    <label for="name" class="form-label">Nombre</label>
    <input
        type="text"
        name="name"
        id="name"
        value="{{ old('name', $day?->name) }}"
        placeholder="Navidad, Sant Ciriac, vacaciones de agosto…"
        class="form-input"
        required
        autofocus
    >
    @error('name') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="starts_on" class="form-label">Primer día</label>
        <input
            type="date"
            name="starts_on"
            id="starts_on"
            value="{{ old('starts_on', $day?->starts_on?->toDateString()) }}"
            class="form-input"
            required
        >
        @error('starts_on') <p class="form-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="ends_on" class="form-label">Último día <span class="text-gray-400 font-normal">(opcional)</span></label>
        <input
            type="date"
            name="ends_on"
            id="ends_on"
            value="{{ old('ends_on', $day?->ends_on?->toDateString()) }}"
            class="form-input"
        >
        @error('ends_on') <p class="form-error">{{ $message }}</p> @enderror
        <p class="mt-1.5 text-xs text-gray-500">Déjalo vacío si es un solo día.</p>
    </div>
</div>

<div>
    <label class="flex items-start gap-2 cursor-pointer">
        <input type="hidden" name="recurs_annually" value="0">
        <input
            type="checkbox"
            name="recurs_annually"
            value="1"
            class="mt-0.5 rounded border-gray-300 text-sky-500 focus:ring-sky-500"
            @checked(old('recurs_annually', $day?->recurs_annually))
        >
        <span class="text-sm text-gray-600">
            Se repite cada año
            <span class="block text-xs text-gray-400">
                Para festivos de fecha fija como el 25 de diciembre. No lo marques en los que cambian de fecha cada año, como el Viernes Santo.
            </span>
        </span>
    </label>
    @error('recurs_annually') <p class="form-error">{{ $message }}</p> @enderror
</div>
