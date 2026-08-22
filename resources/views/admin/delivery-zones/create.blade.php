<x-layouts.admin title="Nueva zona de reparto">
    <div class="max-w-2xl">
        <x-admin.card>
            <form method="POST" action="{{ route('admin.delivery-zones.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="postal_code" class="form-label">Código postal</label>
                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}" class="form-input font-mono" required autofocus>
                        @error('postal_code') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="delivery_fee" class="form-label">Tarifa de envío (EUR)</label>
                        <input type="number" name="delivery_fee" id="delivery_fee" value="{{ old('delivery_fee', 0) }}" class="form-input" step="0.01" min="0" required>
                        @error('delivery_fee') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="neighborhood" class="form-label">Barrio <span class="text-gray-400 font-normal">(opcional)</span></label>
                    <input type="text" name="neighborhood" id="neighborhood" value="{{ old('neighborhood') }}" class="form-input">
                    @error('neighborhood') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="city" class="form-label">Ciudad <span class="text-gray-400 font-normal">(opcional)</span></label>
                    <input type="text" name="city" id="city" value="{{ old('city') }}" class="form-input">
                    @error('city') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <x-admin.delivery-days :selected="old('delivery_days', [])" />

                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-sky-500 focus:ring-sky-500" {{ old('is_active', true) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-600">Zona activa</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Crear zona</button>
                    <a href="{{ route('admin.delivery-zones.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </x-admin.card>
    </div>
</x-layouts.admin>
