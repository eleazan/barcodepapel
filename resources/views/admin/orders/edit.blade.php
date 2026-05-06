<x-layouts.admin title="Editar pedido {{ $order->order_number }}">
    <div class="max-w-3xl" x-data="orderEditForm()">
        <x-admin.card>
            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-6">
                @csrf @method('PUT')

                {{-- Customer info --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Datos del cliente</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_name" class="form-label">Nombre</label>
                            <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name', $order->customer_name) }}" class="form-input" required>
                            @error('customer_name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="customer_phone" class="form-label">Teléfono</label>
                            <input type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone', $order->customer_phone) }}" class="form-input" required>
                            @error('customer_phone') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="customer_email" class="form-label">Email <span class="text-gray-400 font-normal">(opcional)</span></label>
                            <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email', $order->customer_email) }}" class="form-input">
                        </div>
                    </div>
                </div>

                <hr class="border-sky-100">

                {{-- Delivery --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Entrega</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="delivery_address" class="form-label">Dirección</label>
                            <textarea name="delivery_address" id="delivery_address" rows="2" class="form-textarea" required>{{ old('delivery_address', $order->delivery_address) }}</textarea>
                            @error('delivery_address') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="postal_code" class="form-label">Código postal</label>
                            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $order->postal_code) }}" class="form-input" required>
                        </div>
                        <div>
                            <label for="delivery_fee" class="form-label">Costo de envío (EUR)</label>
                            <input type="number" name="delivery_fee" id="delivery_fee" value="{{ old('delivery_fee', $order->delivery_fee) }}" class="form-input" step="0.01" min="0" required x-model="deliveryFee">
                        </div>
                    </div>
                </div>

                <hr class="border-sky-100">

                {{-- Items --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Productos</h3>
                    @error('items') <p class="form-error mb-2">{{ $message }}</p> @enderror

                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex items-start gap-3 p-3 bg-sky-50/50 rounded-xl">
                                <div class="flex-1">
                                    <select :name="`items[${index}][product_id]`" class="form-select text-sm" required x-model="item.product_id" @change="updatePrice(index)">
                                        <option value="">Seleccionar producto</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }} — {{ number_format((float) $product->price, 2, ',', '.') }} €</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-24">
                                    <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" min="1" class="form-input text-sm text-center" required @input="recalculate()">
                                </div>
                                <div class="w-28 text-right pt-2">
                                    <span class="text-sm font-medium text-gray-600" x-text="(item.price * item.quantity).toFixed(2).replace('.', ',') + ' €'"></span>
                                </div>
                                <button type="button" @click="removeItem(index)" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors mt-0.5" x-show="items.length > 1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addItem()" class="mt-3 inline-flex items-center gap-1.5 text-sm text-sky-600 hover:text-sky-800 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Agregar producto
                    </button>

                    <div class="mt-4 pt-4 border-t border-sky-100 space-y-1 text-sm text-right">
                        <p class="text-gray-500">Subtotal: <span class="font-medium text-gray-700" x-text="subtotal.toFixed(2).replace('.', ',') + ' €'"></span></p>
                        <p class="text-gray-500">Envío: <span class="font-medium text-gray-700" x-text="parseFloat(deliveryFee || 0).toFixed(2).replace('.', ',') + ' €'"></span></p>
                        <p class="text-base font-semibold text-gray-800">Total: <span x-text="total.toFixed(2).replace('.', ',') + ' €'"></span></p>
                    </div>
                </div>

                <hr class="border-sky-100">

                {{-- Status & notes --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="form-label">Estado</label>
                        <select name="status" id="status" class="form-select">
                            @foreach (\App\Models\Order::STATUSES as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $order->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="notes" class="form-label">Notas</label>
                        <textarea name="notes" id="notes" rows="2" class="form-textarea">{{ old('notes', $order->notes) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                    <a href="{{ route('admin.orders.show', $order) }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </x-admin.card>
    </div>

    <script>
        function orderEditForm() {
            const productPrices = {
                @foreach ($products as $product)
                    '{{ $product->id }}': {{ $product->price }},
                @endforeach
            };

            return {
                items: [
                    @foreach ($order->items as $item)
                        { product_id: '{{ $item->product_id }}', quantity: {{ $item->quantity }}, price: {{ $item->unit_price }} },
                    @endforeach
                ],
                deliveryFee: {{ $order->delivery_fee }},

                get subtotal() {
                    return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                get total() {
                    return this.subtotal + parseFloat(this.deliveryFee || 0);
                },

                addItem() {
                    this.items.push({ product_id: '', quantity: 1, price: 0 });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                updatePrice(index) {
                    const pid = this.items[index].product_id;
                    this.items[index].price = productPrices[pid] || 0;
                },

                recalculate() {}
            };
        }
    </script>
</x-layouts.admin>
