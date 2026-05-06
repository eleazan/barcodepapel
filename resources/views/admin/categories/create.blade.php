<x-layouts.admin title="Nueva categoría">
    <div class="max-w-2xl">
        <x-admin.card>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-input" required autofocus>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="slug" class="form-label">Slug <span class="text-gray-400 font-normal">(opcional, se genera automáticamente)</span></label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="form-input">
                    @error('slug') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="form-label">Descripción</label>
                    <textarea name="description" id="description" rows="3" class="form-textarea">{{ old('description') }}</textarea>
                    @error('description') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sort_order" class="form-label">Orden</label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" class="form-input" min="0">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-sky-500 focus:ring-sky-500" {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="text-sm text-gray-600">Activa</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Crear categoría</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </x-admin.card>
    </div>
</x-layouts.admin>
