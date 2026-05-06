<x-layouts.admin>
    <x-slot:title>Editar art&iacute;culo</x-slot:title>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Editar art&iacute;culo</h1>
    </div>

    <x-admin.card>
        <div class="card-body">
            <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf @method('PUT')

                <div>
                    <label for="title" class="form-label">T&iacute;tulo <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $post->title) }}" class="form-input" required>
                    @error('title') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $post->slug) }}" class="form-input">
                    @error('slug') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="excerpt" class="form-label">Extracto</label>
                    <textarea id="excerpt" name="excerpt" rows="2" class="form-textarea" maxlength="300">{{ old('excerpt', $post->excerpt) }}</textarea>
                    @error('excerpt') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="body" class="form-label">Contenido <span class="text-red-500">*</span></label>
                    <textarea id="body" name="body" rows="15" class="form-textarea" required>{{ old('body', $post->body) }}</textarea>
                    <p class="form-hint">Puedes usar HTML b&aacute;sico para formatear el contenido.</p>
                    @error('body') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="image" class="form-label">Imagen destacada</label>
                    @if ($post->image)
                        <div class="mb-3">
                            <img src="{{ Storage::url($post->image) }}" alt="" class="w-40 h-24 object-cover rounded-lg">
                        </div>
                    @endif
                    <input type="file" id="image" name="image" accept="image/*" class="form-input">
                    @error('image') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" value="1" {{ old('is_published', $post->is_published) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-700">Publicado</span>
                    </label>
                    @if ($post->published_at)
                        <span class="text-xs text-gray-400">Publicado el {{ $post->published_at->format('d/m/Y H:i') }}</span>
                    @endif
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                    <a href="{{ route('admin.posts.index') }}" class="btn-secondary">Cancelar</a>
                    @if ($post->is_published)
                        <a href="{{ route('blog.show', $post) }}" target="_blank" class="btn-secondary">Ver en el blog</a>
                    @endif
                </div>
            </form>
        </div>
    </x-admin.card>
</x-layouts.admin>
