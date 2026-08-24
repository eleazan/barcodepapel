<x-layouts.admin>
    <x-slot:title>Tareas en segundo plano</x-slot:title>

    @php
        $hayLoteEnCurso = $tareas->contains(fn ($t) => $t['enCurso'] !== null);
    @endphp

    {{-- Con un lote en marcha la página se refresca sola para ver avanzar la barra --}}
    <div class="space-y-6"
         @if ($hayLoteEnCurso) x-data x-init="setTimeout(() => window.location.reload(), 5000)" @endif>

        {{-- Estado de la cola --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <x-admin.stat-card label="Trabajos en cola" :value="$trabajosEnCola" color="sky">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7"/></svg>
                </x-slot:icon>
            </x-admin.stat-card>

            <x-admin.stat-card
                label="Trabajos fallidos"
                :value="$trabajosFallidos"
                :color="$trabajosFallidos > 0 ? 'amber' : 'green'"
            >
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>
            </x-admin.stat-card>

            <x-admin.stat-card label="Tareas registradas" :value="$tareas->count()" color="violet">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </x-slot:icon>
            </x-admin.stat-card>
        </div>

        {{-- Recordatorio: sin worker no se procesa nada --}}
        <div class="flex gap-3 px-4 py-3 bg-brand-50 border border-brand-100 rounded-xl text-sm text-gray-600">
            <svg class="w-5 h-5 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>
                Los lotes solo avanzan mientras haya un procesador de cola en marcha
                (<code class="bg-white px-1.5 py-0.5 rounded font-mono text-xs">php artisan queue:work</code>).
                Si el contador de trabajos en cola no baja, es que nadie los está atendiendo.
            </p>
        </div>

        @forelse ($tareas as $t)
            @php
                $task    = $t['task'];
                $enCurso = $t['enCurso'];
                $sugerido = min(max($t['disponibles'], 1), 200);
            @endphp

            <x-admin.card>
                <x-slot:title>{{ $task->label() }}</x-slot:title>

                <p class="text-sm text-gray-500 mb-5">{{ $task->description() }}</p>

                {{-- Contadores de la tarea --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
                    @foreach ($t['stats'] as $stat)
                        <div class="rounded-xl border border-brand-100 bg-brand-50/40 px-4 py-3">
                            <p class="text-2xl font-bold text-gray-800 tracking-tight">
                                {{ number_format((int) $stat->value, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-500 font-medium mt-0.5">{{ $stat->label }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Límite de la API --}}
                @if ($t['aviso'])
                    <p class="text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 mb-5">
                        {{ $t['aviso'] }}
                    </p>
                @endif

                {{-- Lote en curso --}}
                @if ($enCurso)
                    @php
                        $pct        = $history->progress($enCurso);
                        $procesados = (int) $enCurso->total_jobs - (int) $enCurso->pending_jobs;
                    @endphp
                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 mb-5">
                        <div class="flex items-center justify-between gap-4 mb-2">
                            <div>
                                <p class="text-sm font-semibold text-blue-900">Lote en curso</p>
                                <p class="text-xs text-blue-700 mt-0.5">
                                    {{ number_format($procesados, 0, ',', '.') }}
                                    de {{ number_format((int) $enCurso->total_jobs, 0, ',', '.') }} procesados
                                    @if ((int) $enCurso->failed_jobs > 0)
                                        · <span class="text-red-600 font-medium">{{ $enCurso->failed_jobs }} con error</span>
                                    @endif
                                    · lanzado {{ \Carbon\Carbon::createFromTimestamp($enCurso->created_at)->diffForHumans() }}
                                </p>
                            </div>
                            <form method="POST"
                                  action="{{ route('admin.jobs.cancel', ['task' => $task->key(), 'batch' => $enCurso->id]) }}">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs font-medium text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                                    Cancelar lote
                                </button>
                            </form>
                        </div>
                        <div class="h-2 bg-blue-100 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="text-xs text-blue-700 mt-1.5 text-right font-medium">{{ $pct }} %</p>
                    </div>
                @endif

                {{-- Acciones --}}
                <div class="flex flex-wrap items-end gap-4">
                    <form method="POST" action="{{ route('admin.jobs.run', $task->key()) }}"
                          class="flex flex-wrap items-end gap-3"
                          x-data="{ cantidad: {{ $sugerido }} }">
                        @csrf

                        <div>
                            <label for="cantidad-{{ $task->key() }}" class="block text-xs font-medium text-gray-600 mb-1">
                                Cuántos procesar
                            </label>
                            <input type="number" name="cantidad" id="cantidad-{{ $task->key() }}"
                                   x-model="cantidad" min="1" max="5000"
                                   @disabled($enCurso || $t['disponibles'] === 0)
                                   class="w-28 px-3 py-2 text-sm border border-gray-200 rounded-lg
                                          focus:border-brand-400 focus:ring-1 focus:ring-brand-400
                                          disabled:bg-gray-50 disabled:text-gray-400">
                            @error('cantidad')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if (! $enCurso && $t['disponibles'] > 0)
                            <div class="flex gap-1 pb-1">
                                @foreach ([50, 200, 1000] as $preset)
                                    @if ($preset < $t['disponibles'])
                                        <button type="button" @click="cantidad = {{ $preset }}"
                                                class="px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                                            {{ $preset }}
                                        </button>
                                    @endif
                                @endforeach
                                <button type="button" @click="cantidad = {{ $t['disponibles'] }}"
                                        class="px-2 py-1 text-xs text-brand-700 bg-brand-100 rounded-md hover:bg-brand-200 transition-colors">
                                    Todos ({{ number_format($t['disponibles'], 0, ',', '.') }})
                                </button>
                            </div>
                        @endif

                        <button type="submit"
                                @disabled($enCurso || $t['disponibles'] === 0)
                                class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium
                                       rounded-lg transition-colors disabled:bg-gray-200 disabled:text-gray-400
                                       disabled:cursor-not-allowed">
                            Procesar pendientes
                        </button>
                    </form>

                    @if ($task instanceof \App\Services\Jobs\ResettableTask && $task->discardedCount() > 0)
                        <form method="POST" action="{{ route('admin.jobs.reset', $task->key()) }}"
                              onsubmit="return confirm('¿Devolver a pendientes los {{ $task->discardedCount() }} elementos descartados?')">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200
                                           rounded-lg hover:bg-gray-50 transition-colors">
                                {{ $task->resetLabel() }} ({{ number_format($task->discardedCount(), 0, ',', '.') }})
                            </button>
                        </form>
                    @endif
                </div>

                @if ($t['pendientes'] === 0)
                    <p class="mt-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                        Nada pendiente: todos los elementos de esta tarea están resueltos o descartados.
                    </p>
                @elseif ($t['disponibles'] < $t['pendientes'])
                    <p class="mt-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        Quedan {{ number_format($t['pendientes'], 0, ',', '.') }} pendientes, pero hoy solo se pueden
                        lanzar {{ number_format($t['disponibles'], 0, ',', '.') }} por el límite de la API. El resto,
                        cuando se renueve la cuota.
                    </p>
                @endif

                {{-- Historial de lotes --}}
                @if ($t['lotes']->isNotEmpty())
                    <div class="mt-6 pt-5 border-t border-brand-100">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Últimos lotes</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs text-gray-400 uppercase tracking-wide">
                                        <th class="pb-2 pr-4 font-medium">Lanzado</th>
                                        <th class="pb-2 pr-4 font-medium">Trabajos</th>
                                        <th class="pb-2 pr-4 font-medium">Errores</th>
                                        <th class="pb-2 pr-4 font-medium">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($t['lotes'] as $lote)
                                        <tr>
                                            <td class="py-2 pr-4 text-gray-500 whitespace-nowrap">
                                                {{ \Carbon\Carbon::createFromTimestamp($lote->created_at)->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="py-2 pr-4 text-gray-700">
                                                {{ number_format((int) $lote->total_jobs - (int) $lote->pending_jobs, 0, ',', '.') }}
                                                / {{ number_format((int) $lote->total_jobs, 0, ',', '.') }}
                                            </td>
                                            <td class="py-2 pr-4">
                                                @if ((int) $lote->failed_jobs > 0)
                                                    <span class="text-red-600 font-medium">{{ $lote->failed_jobs }}</span>
                                                @else
                                                    <span class="text-gray-300">—</span>
                                                @endif
                                            </td>
                                            <td class="py-2 pr-4">
                                                @if ($lote->cancelled_at)
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Cancelado</span>
                                                @elseif ($lote->finished_at)
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Terminado</span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">En curso</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </x-admin.card>
        @empty
            <x-admin.card>
                <x-admin.empty-state
                    message="No hay tareas registradas. Se registran en AppServiceProvider."
                />
            </x-admin.card>
        @endforelse

    </div>
</x-layouts.admin>
