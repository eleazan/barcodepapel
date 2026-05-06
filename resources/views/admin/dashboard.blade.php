<x-layouts.admin title="Dashboard">

    {{-- Stats grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-admin.stat-card label="Productos activos" :value="$activeProducts" color="sky">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card label="Pedidos totales" :value="$totalOrders" color="violet">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card label="Pendientes" :value="$pendingOrders" color="amber">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card :label="'Facturación total'" :value="number_format((float) $totalRevenue, 2, ',', '.') . ' €'" color="green">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.121 15.536c-1.171 1.952-3.07 1.952-4.242 0-1.172-1.953-1.172-5.119 0-7.072 1.171-1.952 3.07-1.952 4.242 0M8 10.5h4m-4 3h4m9-1.5a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-admin.stat-card>
    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Sales last 7 days --}}
        <x-admin.card title="Ventas últimos 7 días" class="lg:col-span-2">
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </x-admin.card>

        {{-- Orders by status --}}
        <x-admin.card title="Pedidos por estado">
            <div class="h-64 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
        </x-admin.card>
    </div>

    {{-- Top products --}}
    @if ($topProducts->count())
        <div class="mb-8">
            <x-admin.card title="Productos más vendidos" :padding="false">
                <div class="divide-y divide-sky-50">
                    @foreach ($topProducts as $i => $item)
                        <div class="flex items-center gap-4 px-6 py-3.5">
                            <span class="w-7 h-7 rounded-lg bg-sky-50 text-sky-500 flex items-center justify-center text-xs font-bold shrink-0">{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-700 truncate">{{ $item->product->name ?? '—' }}</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-600">{{ $item->total_sold }} uds.</span>
                        </div>
                    @endforeach
                </div>
            </x-admin.card>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent orders --}}
        <x-admin.card title="Pedidos recientes" :padding="false">
            @if ($recentOrders->count())
                <div class="divide-y divide-sky-50">
                    @foreach ($recentOrders as $order)
                        <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between px-6 py-3.5 hover:bg-sky-50/40 transition-colors">
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ $order->order_number }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $order->customer_name }} &middot; {{ $order->formattedTotal() }}</p>
                            </div>
                            <x-admin.status-badge :status="$order->status" />
                        </a>
                    @endforeach
                </div>
            @else
                <x-admin.empty-state message="No hay pedidos todavía." action="Crear pedido" :actionUrl="route('admin.orders.create')" />
            @endif
        </x-admin.card>

        {{-- Low stock --}}
        <x-admin.card title="Stock bajo (≤5 unidades)" :padding="false">
            @if ($lowStockProducts->count())
                <div class="divide-y divide-sky-50">
                    @foreach ($lowStockProducts as $product)
                        <a href="{{ route('admin.products.edit', $product) }}" class="flex items-center justify-between px-6 py-3.5 hover:bg-sky-50/40 transition-colors">
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ $product->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $product->category->name ?? '—' }} &middot; {{ $product->formattedPrice() }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold ring-1 ring-inset
                                {{ $product->stock === 0 ? 'bg-red-50 text-red-600 ring-red-200' : 'bg-amber-50 text-amber-600 ring-amber-200' }}">
                                {{ $product->stock }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <x-admin.empty-state message="Todo el stock está bien." />
            @endif
        </x-admin.card>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fontFamily = 'Inter, system-ui, sans-serif';

            Chart.defaults.font.family = fontFamily;
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#9ca3af';

            // Sales bar chart
            new Chart(document.getElementById('salesChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($salesChart->pluck('label')) !!},
                    datasets: [{
                        label: 'Ventas (€)',
                        data: {!! json_encode($salesChart->pluck('revenue')) !!},
                        backgroundColor: 'rgba(14, 165, 233, 0.15)',
                        borderColor: 'rgb(14, 165, 233)',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.parsed.y.toFixed(2).replace('.', ',') + ' €'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: {
                                callback: v => v.toFixed(0) + ' €'
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });

            // Status doughnut
            const statusData = {!! json_encode($ordersByStatus) !!};
            const statusLabels = {!! json_encode(collect(\App\Models\Order::STATUSES)) !!};
            const statusColors = {
                pendiente: '#f59e0b',
                preparado: '#0ea5e9',
                en_reparto: '#8b5cf6',
                entregado: '#10b981',
            };

            const labels = Object.keys(statusData).map(k => statusLabels[k] || k);
            const values = Object.values(statusData);
            const colors = Object.keys(statusData).map(k => statusColors[k] || '#9ca3af');

            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderWidth: 0,
                        spacing: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 16, usePointStyle: true, pointStyleWidth: 8 }
                        }
                    }
                }
            });
        });
    </script>

</x-layouts.admin>
