<x-layouts.admin>
    <x-slot:title>Blog</x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Blog</h1>
            <p class="text-sm text-gray-500 mt-1">Gestiona los art&iacute;culos del blog</p>
        </div>
        <a href="{{ route('admin.posts.create') }}" class="btn-primary">Nuevo art&iacute;culo</a>
    </div>

    {{-- Search --}}
    <form action="{{ route('admin.posts.index') }}" method="GET" class="mb-6">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar art&iacute;culos..." class="form-input max-w-sm">
    </form>

    <x-admin.card>
        <x-admin.table>
            <thead>
                <tr>
                    <x-admin.th>T&iacute;tulo</x-admin.th>
                    <x-admin.th>Autor</x-admin.th>
                    <x-admin.th>Estado</x-admin.th>
                    <x-admin.th>Fecha</x-admin.th>
                    <x-admin.th class="text-right">Acciones</x-admin.th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <x-admin.td>
                            <div class="flex items-center gap-3">
                                @if ($post->image)
                                    <img src="{{ Storage::url($post->image) }}" alt="" class="w-10 h-10 rounded-lg object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $post->title }}</p>
                                    <p class="text-xs text-gray-400">/blog/{{ $post->slug }}</p>
                                </div>
                            </div>
                        </x-admin.td>
                        <x-admin.td>{{ $post->author->name }}</x-admin.td>
                        <x-admin.td>
                            @if ($post->is_published)
                                <span class="badge-success">Publicado</span>
                            @else
                                <span class="badge-warning">Borrador</span>
                            @endif
                        </x-admin.td>
                        <x-admin.td>{{ $post->published_at?->format('d/m/Y H:i') ?? '—' }}</x-admin.td>
                        <x-admin.td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if ($post->is_published)
                                    <a href="{{ route('blog.show', $post) }}" target="_blank" class="btn-secondary btn-sm">Ver</a>
                                @endif
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn-secondary btn-sm">Editar</a>
                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('&iquest;Eliminar este art&iacute;culo?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </x-admin.td>
                    </tr>
                @empty
                    <tr>
                        <x-admin.td colspan="5">
                            <x-admin.empty-state message="No hay art&iacute;culos a&uacute;n" />
                        </x-admin.td>
                    </tr>
                @endforelse
            </tbody>
        </x-admin.table>
    </x-admin.card>

    @if ($posts->hasPages())
        <div class="mt-6">{{ $posts->links() }}</div>
    @endif
</x-layouts.admin>
