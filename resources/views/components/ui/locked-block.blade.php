@props(['label'])

{{-- Stands in for private data. It never holds the real value, not even blurred. --}}
<div {{ $attributes->class('flex items-center gap-4 rounded-xl border-2 border-dashed border-line-brand bg-tint p-4 text-content-soft') }}>
    <x-heroicon-o-lock-closed class="size-6 shrink-0 text-accent" />
    <div>
        <p class="font-semibold">{{ $label }}</p>
        <p class="text-sm text-content-muted">{{ __('ui.locked') }}</p>
    </div>
</div>
