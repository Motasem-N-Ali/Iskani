@props(['label'])

{{-- Stands in for private data. It never holds the real value, not even blurred. --}}
<div {{ $attributes->class('flex items-center gap-4 rounded-xl border-2 border-dashed border-primary-300 bg-primary-50 p-4 text-steel-800') }}>
    <x-heroicon-o-lock-closed class="size-6 shrink-0 text-primary-600" />
    <div>
        <p class="font-semibold">{{ $label }}</p>
        <p class="text-sm text-steel-600">{{ __('ui.locked') }}</p>
    </div>
</div>
