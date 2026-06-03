<x-layouts.admin title="Editar producto">
    <div class="max-w-2xl">
        <x-admin.card>
            <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf @method('PUT')

                <div>
                    <label for="category_id" class="form-label">Categoría</label>
                    <select name="category_id" id="category_id" class="form-select" required>
                        <option value="">Seleccionar categoría</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" class="form-input" required autofocus>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sku" class="form-label">SKU</label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" class="form-input">
                        @error('sku') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="slug" class="form-label">Slug</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}" class="form-input">
                        @error('slug') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="form-label">Descripción</label>
                    <textarea name="description" id="description" rows="4" class="form-textarea">{{ old('description', $product->description) }}</textarea>
                    @error('description') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="form-label">Precio (EUR)</label>
                        <input type="number" name="price" id="price" value="{{ old('price', $product->price) }}" class="form-input" step="0.01" min="0" required>
                        @error('price') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}" class="form-input" min="0" required>
                        @error('stock') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="image" class="form-label">Imagen principal</label>
                    @if ($product->image)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ $product->image_url }}" alt="" class="w-16 h-16 rounded-lg object-cover bg-sky-50">
                            <span class="text-xs text-gray-400">Imagen actual</span>
                        </div>
                    @endif
                    <input type="file" name="image" id="image" accept="image/*" class="form-input py-1.5 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100">
                    @error('image') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Galería de imágenes</label>
                    @if ($product->images->count())
                        <div class="flex flex-wrap gap-3 mb-3" x-data="{ toDelete: [] }">
                            @foreach ($product->images as $img)
                                <label class="relative group cursor-pointer">
                                    <input type="checkbox" name="delete_images[]" value="{{ $img->id }}" class="sr-only peer">
                                    <img src="{{ $img->url() }}" alt="" class="w-20 h-20 rounded-lg object-cover bg-sky-50 ring-2 ring-transparent peer-checked:ring-red-400 peer-checked:opacity-40 transition-all">
                                    <span class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </span>
                                </label>
                            @endforeach
                            <p class="w-full text-xs text-gray-400 mt-1">Haz clic en una imagen para marcarla para eliminar</p>
                        </div>
                    @endif
                    <input type="file" name="images[]" id="images" accept="image/*" multiple class="form-input py-1.5 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100">
                    <p class="form-hint">Puedes agregar más imágenes (máx. 10 total)</p>
                    @error('images') <p class="form-error">{{ $message }}</p> @enderror
                    @error('images.*') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-sky-500 focus:ring-sky-500" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-600">Producto activo</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                    <a href="{{ route('admin.products.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </x-admin.card>
    </div>
</x-layouts.admin>
