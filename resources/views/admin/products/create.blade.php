<x-layouts.admin title="Nuevo producto">
    <div class="max-w-2xl">
        <x-admin.card>
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label for="category_id" class="form-label">Categoría</label>
                    <select name="category_id" id="category_id" class="form-select" required>
                        <option value="">Seleccionar categoría</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-input" required autofocus>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sku" class="form-label">SKU <span class="text-gray-400 font-normal">(opcional)</span></label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="form-input">
                        @error('sku') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="slug" class="form-label">Slug <span class="text-gray-400 font-normal">(opcional)</span></label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="form-input">
                        @error('slug') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="form-label">Descripción</label>
                    <textarea name="description" id="description" rows="4" class="form-textarea">{{ old('description') }}</textarea>
                    @error('description') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="form-label">Precio (EUR)</label>
                        <input type="number" name="price" id="price" value="{{ old('price') }}" class="form-input" step="0.01" min="0" required>
                        @error('price') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" name="stock" id="stock" value="{{ old('stock', 0) }}" class="form-input" min="0" required>
                        @error('stock') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="image" class="form-label">Imagen principal</label>
                    <input type="file" name="image" id="image" accept="image/*" class="form-input py-1.5 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100">
                    @error('image') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="images" class="form-label">Galería de imágenes <span class="text-gray-400 font-normal">(opcional, máx. 10)</span></label>
                    <input type="file" name="images[]" id="images" accept="image/*" multiple class="form-input py-1.5 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100">
                    @error('images') <p class="form-error">{{ $message }}</p> @enderror
                    @error('images.*') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-sky-500 focus:ring-sky-500" {{ old('is_active', true) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-600">Producto activo</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Crear producto</button>
                    <a href="{{ route('admin.products.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </x-admin.card>
    </div>
</x-layouts.admin>
