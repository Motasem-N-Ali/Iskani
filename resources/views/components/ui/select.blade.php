@props(['label', 'name'])

{{-- A styled native select: works with the keyboard and screen readers out of the box. --}}
<div>
    <label for="{{ $name }}" class="mb-2 block text-lg font-semibold text-content-soft">{{ $label }}</label>

    <div class="relative">
        <select id="{{ $name }}" name="{{ $name }}"
            {{ $attributes->class('h-14 w-full appearance-none rounded-xl border-2 border-accent bg-surface ps-4 pe-12 text-lg text-content focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent') }}>
            {{ $slot }}
        </select>
        <x-heroicon-o-chevron-down class="pointer-events-none absolute end-4 top-1/2 size-5 -translate-y-1/2 text-accent" />
    </div>
</div>
