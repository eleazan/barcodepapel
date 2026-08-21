{{-- Mensajes flash de la tienda pública --}}
@if (session('success') || session('error') || $errors->any())
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 lg:pt-28 -mb-16 lg:-mb-20">
        @if (session('success'))
            <div
                x-data="{ visible: true }"
                x-show="visible"
                x-init="setTimeout(() => visible = false, 6000)"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-end="opacity-0"
                class="flex items-start gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 mb-3"
                role="status"
            >
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
                <button type="button" @click="visible = false" class="ml-auto shrink-0 text-emerald-600 hover:text-emerald-800" aria-label="Cerrar aviso">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-start gap-3 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 mb-3" role="alert">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 mb-3" role="alert">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-sm font-semibold mb-1">Revisa estos campos:</p>
                        <ul class="text-sm space-y-0.5 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
