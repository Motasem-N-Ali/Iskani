@props(['variant' => 'primary', 'href' => null, 'type' => 'button'])

@php
    $classes = 'group inline-flex h-14 items-center justify-center gap-3 rounded-xl px-8 text-lg font-semibold transition duration-200 active:scale-[0.98] disabled:active:scale-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:cursor-not-allowed disabled:bg-disabled-bg disabled:text-disabled-fg disabled:hover:bg-disabled-bg '.(match ($variant) {
        'secondary' => 'border-2 border-accent bg-surface text-accent hover:bg-tint disabled:border-disabled-bg',
        'ghost' => 'text-accent hover:bg-tint',
        default => 'bg-primary-600 text-paper hover:bg-primary-700',
    });
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
