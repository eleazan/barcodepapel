<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-sky-100 shadow-sm overflow-hidden']) }}>
    @if (isset($header))
        <div class="px-6 py-4 border-b border-sky-50 flex items-center justify-between gap-4">
            {{ $header }}
        </div>
    @endif
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-sky-50 bg-sky-50/30">
                    {{ $head }}
                </tr>
            </thead>
            <tbody class="divide-y divide-sky-50">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @if (isset($pagination))
        <div class="px-6 py-3 border-t border-sky-50">
            {{ $pagination }}
        </div>
    @endif
</div>
