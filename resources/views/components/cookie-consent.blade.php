<div
    x-data="{
        show: false,
        preferences: false,
        consent: { necessary: true, analytics: false, marketing: false },
        init() {
            const stored = localStorage.getItem('cookie_consent');
            if (!stored) {
                setTimeout(() => this.show = true, 1000);
            }
        },
        acceptAll() {
            this.consent = { necessary: true, analytics: true, marketing: true };
            this.save();
        },
        acceptSelected() {
            this.consent.necessary = true;
            this.save();
        },
        rejectAll() {
            this.consent = { necessary: true, analytics: false, marketing: false };
            this.save();
        },
        save() {
            localStorage.setItem('cookie_consent', JSON.stringify(this.consent));
            localStorage.setItem('cookie_consent_date', new Date().toISOString());
            this.show = false;
            this.preferences = false;
            window.dispatchEvent(new CustomEvent('cookie-consent-updated', { detail: this.consent }));
        }
    }"
    x-show="show"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-full opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-full opacity-0"
    class="fixed bottom-0 inset-x-0 z-[60] p-4 sm:p-6"
    role="dialog"
    aria-label="Consentimiento de cookies"
>
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-2xl shadow-gray-900/10 border border-gray-100 overflow-hidden">
        {{-- Main banner --}}
        <div x-show="!preferences" class="p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-sm font-semibold text-gray-900 mb-1">Utilizamos cookies</h2>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Usamos cookies propias y de terceros para mejorar tu experiencia, analizar el tr&aacute;fico y personalizar el contenido. Puedes aceptar todas, configurarlas o rechazarlas.
                    </p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 mt-4">
                <button @click="acceptAll()" class="px-5 py-2.5 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition-colors">Aceptar todas</button>
                <button @click="preferences = true" class="px-5 py-2.5 text-gray-700 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">Configurar</button>
                <button @click="rejectAll()" class="px-5 py-2.5 text-gray-500 text-sm rounded-xl hover:text-gray-700 transition-colors">Solo necesarias</button>
            </div>
        </div>

        {{-- Preferences panel --}}
        <div x-show="preferences" x-cloak class="p-5 sm:p-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Configurar cookies</h2>
            <div class="space-y-4">
                {{-- Necessary --}}
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Necesarias</p>
                        <p class="text-xs text-gray-500">Imprescindibles para el funcionamiento del sitio.</p>
                    </div>
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Siempre activas</span>
                </div>

                {{-- Analytics --}}
                <label class="flex items-center justify-between gap-4 cursor-pointer">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Anal&iacute;ticas</p>
                        <p class="text-xs text-gray-500">Nos ayudan a entender c&oacute;mo usas el sitio.</p>
                    </div>
                    <div class="relative">
                        <input type="checkbox" x-model="consent.analytics" class="sr-only peer">
                        <div class="w-10 h-6 bg-gray-200 peer-checked:bg-brand-600 rounded-full transition-colors"></div>
                        <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                    </div>
                </label>

                {{-- Marketing --}}
                <label class="flex items-center justify-between gap-4 cursor-pointer">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Marketing</p>
                        <p class="text-xs text-gray-500">Permiten mostrarte contenido personalizado.</p>
                    </div>
                    <div class="relative">
                        <input type="checkbox" x-model="consent.marketing" class="sr-only peer">
                        <div class="w-10 h-6 bg-gray-200 peer-checked:bg-brand-600 rounded-full transition-colors"></div>
                        <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                    </div>
                </label>
            </div>
            <div class="flex items-center gap-2 mt-5 pt-4 border-t border-gray-100">
                <button @click="acceptSelected()" class="px-5 py-2.5 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition-colors">Guardar preferencias</button>
                <button @click="preferences = false" class="px-5 py-2.5 text-gray-500 text-sm rounded-xl hover:text-gray-700 transition-colors">Volver</button>
            </div>
        </div>
    </div>
</div>
