<x-layouts.admin title="Editar zona de reparto">
    <div class="max-w-2xl">
        <x-admin.card>
            <form method="POST" action="{{ route('admin.delivery-zones.update', $zone) }}" class="space-y-5">
                @csrf @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="postal_code" class="form-label">Código postal</label>
                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $zone->postal_code) }}" class="form-input font-mono" required autofocus>
                        @error('postal_code') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="delivery_fee" class="form-label">Tarifa de envío (EUR)</label>
                        <input type="number" name="delivery_fee" id="delivery_fee" value="{{ old('delivery_fee', $zone->delivery_fee) }}" class="form-input" step="0.01" min="0" required>
                        @error('delivery_fee') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="neighborhood" class="form-label">Colonia</label>
                    <input type="text" name="neighborhood" id="neighborhood" value="{{ old('neighborhood', $zone->neighborhood) }}" class="form-input">
                    @error('neighborhood') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="city" class="form-label">Ciudad</label>
                    <input type="text" name="city" id="city" value="{{ old('city', $zone->city) }}" class="form-input">
                    @error('city') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-sky-500 focus:ring-sky-500" {{ old('is_active', $zone->is_active) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-600">Zona activa</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                    <a href="{{ route('admin.delivery-zones.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </x-admin.card>
    </div>
</x-layouts.admin>
