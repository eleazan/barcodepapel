<x-layouts.admin title="Productos">
    <x-slot:actions>
        <a href="{{ route('admin.products.create') }}" class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo producto
        </a>
    </x-slot:actions>

    <x-admin.table>
        <x-slot:header>
            <form method="GET" action="{{ route('admin.products.index') }}" class="flex items-center gap-3 w-full">
                <div class="relative flex-1 max-w-xs">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar producto..." class="form-input pl-9 py-2">
                </div>
                <select name="category" class="form-select py-2 w-auto" onchange="this.form.submit()">
                    <option value="">Todas las categorías</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </form>
        </x-slot:header>

        <x-slot:head>
            <x-admin.th>Producto</x-admin.th>
            <x-admin.th>Categoría</x-admin.th>
            <x-admin.th align="right">Precio</x-admin.th>
            <x-admin.th align="center">Stock</x-admin.th>
            <x-admin.th align="center">Estado</x-admin.th>
            <x-admin.th align="right">Acciones</x-admin.th>
        </x-slot:head>

        @forelse ($products as $product)
            <tr class="hover:bg-sky-50/30 transition-colors">
                <x-admin.td>
                    <div class="flex items-center gap-3">
                        @if ($product->image)
                            <img src="{{ $product->image_url }}" alt="" class="w-10 h-10 rounded-lg object-cover bg-sky-50">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                        <div>
                            <p class="font-medium text-gray-700">{{ $product->name }}</p>
                            @if ($product->sku)
                                <p class="text-xs text-gray-400">SKU: {{ $product->sku }}</p>
                            @endif
                        </div>
                    </div>
                </x-admin.td>
                <x-admin.td>
                    <span class="text-gray-500">{{ $product->category->name }}</span>
                </x-admin.td>
                <x-admin.td align="right">
                    <span class="font-medium text-gray-700">{{ $product->formattedPrice() }}</span>
                </x-admin.td>
                <x-admin.td align="center">
                    <span @class([
                        'inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold',
                        'bg-red-50 text-red-600' => $product->stock === 0,
                        'bg-amber-50 text-amber-600' => $product->stock > 0 && $product->stock <= 5,
                        'text-gray-500' => $product->stock > 5,
                    ])>{{ $product->stock }}</span>
                </x-admin.td>
                <x-admin.td align="center">
                    @if ($product->is_active)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-600">Activo</span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-500">Inactivo</span>
                    @endif
                </x-admin.td>
                <x-admin.td align="right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('admin.products.edit', $product) }}" class="p-2 text-gray-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-colors" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" x-data
                              x-on:submit.prevent="if (confirm('¿Eliminar este producto?')) $el.submit()">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </x-admin.td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-admin.empty-state message="No hay productos." action="Crear producto" :actionUrl="route('admin.products.create')" />
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $products->links() }}
        </x-slot:pagination>
    </x-admin.table>
</x-layouts.admin>
