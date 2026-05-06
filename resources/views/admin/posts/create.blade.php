<x-layouts.admin>
    <x-slot:title>Nuevo art&iacute;culo</x-slot:title>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Nuevo art&iacute;culo</h1>
    </div>

    <x-admin.card>
        <div class="card-body">
            <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label for="title" class="form-label">T&iacute;tulo <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-input" required>
                    @error('title') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="excerpt" class="form-label">Extracto</label>
                    <textarea id="excerpt" name="excerpt" rows="2" class="form-textarea" maxlength="300" placeholder="Resumen breve del art&iacute;culo (m&aacute;x. 300 caracteres)">{{ old('excerpt') }}</textarea>
                    @error('excerpt') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="body" class="form-label">Contenido <span class="text-red-500">*</span></label>
                    <textarea id="body" name="body" rows="15" class="form-textarea" required>{{ old('body') }}</textarea>
                    <p class="form-hint">Puedes usar HTML b&aacute;sico para formatear el contenido.</p>
                    @error('body') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="image" class="form-label">Imagen destacada</label>
                    <input type="file" id="image" name="image" accept="image/*" class="form-input">
                    @error('image') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-700">Publicar inmediatamente</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn-primary">Crear art&iacute;culo</button>
                    <a href="{{ route('admin.posts.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </x-admin.card>
</x-layouts.admin>
