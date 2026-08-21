<x-layouts.admin>
    <x-slot:title>Conector Verial</x-slot:title>

    <div class="space-y-6">

        {{-- Alerta: no configurado --}}
        @if (! $isConfigured)
            <div class="flex gap-4 p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                <div class="shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-amber-800">Conector no configurado</p>
                    <p class="mt-1 text-sm text-amber-700">
                        Configura <code class="bg-amber-100 px-1 rounded font-mono text-xs">VERIAL_HOST</code>,
                        <code class="bg-amber-100 px-1 rounded font-mono text-xs">VERIAL_PORT</code> y
                        <code class="bg-amber-100 px-1 rounded font-mono text-xs">VERIAL_SESSION</code>
                        en el archivo <strong>.env</strong> para activar la sincronización con Verial.
                    </p>
                </div>
            </div>
        @endif

        {{-- Cards de estado --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-admin.stat-card
                label="Productos sincronizados"
                :value="$totalSyncronizados"
                color="teal"
            >
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-slot:icon>
            </x-admin.stat-card>

            <x-admin.stat-card
                label="Sin sincronizar"
                :value="$totalSinSincronizar"
                color="gray"
            >
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </x-slot:icon>
            </x-admin.stat-card>

            <x-admin.stat-card
                label="Última sincronización"
                :value="$lastSync ? \Carbon\Carbon::parse($lastSync)->diffForHumans() : 'Nunca'"
                color="blue"
            >
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-slot:icon>
            </x-admin.stat-card>

            <x-admin.stat-card
                label="Errores (24 h)"
                :value="$erroresUltimas24h"
                :color="$erroresUltimas24h > 0 ? 'red' : 'green'"
            >
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-slot:icon>
            </x-admin.stat-card>
        </div>

        {{-- Panel de acciones (solo cuando está configurado) --}}
        @if ($isConfigured)
            <x-admin.card>
                <x-slot:title>Acciones de sincronización</x-slot:title>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    {{-- Catálogo completo --}}
                    <form method="POST" action="{{ route('admin.verial.sync-catalog') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex flex-col items-center gap-2 px-4 py-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Catálogo completo
                        </button>
                    </form>

                    {{-- Catálogo incremental --}}
                    <form method="POST" action="{{ route('admin.verial.sync-catalog-incremental') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex flex-col items-center gap-2 px-4 py-4 bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Catálogo (hoy)
                        </button>
                    </form>

                    {{-- Stock --}}
                    <form method="POST" action="{{ route('admin.verial.sync-stock') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex flex-col items-center gap-2 px-4 py-4 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Actualizar stock
                        </button>
                    </form>

                    {{-- Imágenes --}}
                    <form method="POST" action="{{ route('admin.verial.sync-images') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex flex-col items-center gap-2 px-4 py-4 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Sincronizar imágenes
                        </button>
                    </form>

                    {{-- Pedidos pendientes --}}
                    <form method="POST" action="{{ route('admin.verial.send-orders') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex flex-col items-center gap-2 px-4 py-4 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Enviar pedidos
                        </button>
                    </form>

                    {{-- Estado pedidos --}}
                    <form method="POST" action="{{ route('admin.verial.sync-order-status') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex flex-col items-center gap-2 px-4 py-4 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 4h.01M9 16h.01"/>
                            </svg>
                            Estado pedidos
                        </button>
                    </form>
                </div>
            </x-admin.card>
        @endif

        {{-- Importación manual por CSV --}}
        <x-admin.card>
            <x-slot:title>Importar desde CSV de Verial</x-slot:title>

            <p class="text-sm text-gray-500 mb-5">
                Útil cuando el conector API no está disponible. Exporta desde Verial el
                <strong>listado de stock</strong> y el <strong>listado de tarifas</strong> y súbelos aquí.
                Cada archivo se procesa en segundo plano; el resultado aparece en el registro inferior.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- CSV de Stock --}}
                <div class="rounded-xl border border-green-200 bg-green-50 p-5">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <h3 class="font-semibold text-green-900">CSV de Stock</h3>
                    </div>
                    <p class="text-xs text-green-700 mb-4">
                        «Listado con el stock actual para hacer recuento» — actualiza el campo
                        <code class="bg-green-100 px-1 rounded font-mono">stock</code> de cada producto.
                        Crea el producto como inactivo si no existe aún.
                    </p>
                    <form method="POST" action="{{ route('admin.verial.upload-stock') }}"
                          enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-green-800 mb-1">
                                Archivo CSV (máx. 20 MB)
                            </label>
                            <input type="file" name="csv" accept=".csv,.txt"
                                   class="block w-full text-sm text-gray-600
                                          file:mr-3 file:py-1.5 file:px-3
                                          file:rounded-lg file:border-0
                                          file:text-xs file:font-medium
                                          file:bg-green-600 file:text-white
                                          hover:file:bg-green-700 cursor-pointer">
                            @error('csv')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                                class="w-full py-2 px-4 bg-green-600 hover:bg-green-700
                                       text-white text-sm font-medium rounded-lg transition-colors">
                            Subir y procesar stock
                        </button>
                    </form>
                </div>

                {{-- CSV de Precios --}}
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="font-semibold text-blue-900">CSV de Precios</h3>
                    </div>
                    <p class="text-xs text-blue-700 mb-4">
                        «Listado de tarifas» — actualiza el campo
                        <code class="bg-blue-100 px-1 rounded font-mono">price</code> de cada producto.
                        Activa automáticamente los productos que tengan stock y precio mayor que 0.
                    </p>
                    <form method="POST" action="{{ route('admin.verial.upload-prices') }}"
                          enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-blue-800 mb-1">
                                Archivo CSV (máx. 20 MB)
                            </label>
                            <input type="file" name="csv" accept=".csv,.txt"
                                   class="block w-full text-sm text-gray-600
                                          file:mr-3 file:py-1.5 file:px-3
                                          file:rounded-lg file:border-0
                                          file:text-xs file:font-medium
                                          file:bg-blue-600 file:text-white
                                          hover:file:bg-blue-700 cursor-pointer">
                            @error('csv')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                                class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700
                                       text-white text-sm font-medium rounded-lg transition-colors">
                            Subir y procesar precios
                        </button>
                    </form>
                </div>

            </div>
        </x-admin.card>

        {{-- Tabla de logs recientes --}}
        <x-admin.card>
            <x-slot:title>Registro de sincronizaciones recientes</x-slot:title>

            @if ($logs->isEmpty())
                <x-admin.empty-state
                    title="Sin registros"
                    description="Aún no se ha realizado ninguna sincronización con Verial."
                />
            @else
                <x-admin.table>
                    <x-slot:head>
                        <x-admin.th>Fecha y hora</x-admin.th>
                        <x-admin.th>Entidad</x-admin.th>
                        <x-admin.th>Operación</x-admin.th>
                        <x-admin.th>Método Verial</x-admin.th>
                        <x-admin.th>Estado</x-admin.th>
                        <x-admin.th>Registros</x-admin.th>
                        <x-admin.th>Error</x-admin.th>
                    </x-slot:head>

                    <x-slot:body>
                        @foreach ($logs as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <x-admin.td>
                                    <span class="text-xs text-gray-500 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                                    </span>
                                </x-admin.td>

                                <x-admin.td>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $log->entity_type }}
                                        @if ($log->entity_id)
                                            #{{ $log->entity_id }}
                                        @endif
                                    </span>
                                </x-admin.td>

                                <x-admin.td>
                                    <span class="text-sm text-gray-700">{{ $log->operation }}</span>
                                </x-admin.td>

                                <x-admin.td>
                                    <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded font-mono text-gray-600">
                                        {{ $log->verial_method }}
                                    </code>
                                </x-admin.td>

                                <x-admin.td>
                                    @if ($log->status === 'ok')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            OK
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                            Error
                                        </span>
                                    @endif
                                </x-admin.td>

                                <x-admin.td>
                                    @php
                                        $resp = $log->verial_response ?? [];
                                        $processed = $resp['processed'] ?? null;
                                    @endphp
                                    @if ($processed !== null)
                                        <span class="text-sm text-gray-600">{{ $processed }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </x-admin.td>

                                <x-admin.td>
                                    @if ($log->error_message)
                                        <span class="text-xs text-red-600 line-clamp-2 max-w-xs" title="{{ $log->error_message }}">
                                            {{ $log->error_message }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </x-admin.td>
                            </tr>
                        @endforeach
                    </x-slot:body>
                </x-admin.table>
            @endif
        </x-admin.card>

    </div>
</x-layouts.admin>
