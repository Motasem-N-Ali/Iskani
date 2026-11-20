@props(['variant' => 'primary', 'href' => null, 'type' => 'button'])

@php
    $classes = 'inline-flex h-14 items-center justify-center gap-3 rounded-xl px-8 text-lg font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:cursor-not-allowed disabled:opacity-50 '.(match ($variant) {
        'secondary' => 'border-2 border-primary-600 bg-paper text-primary-600 hover:bg-primary-50',
        'ghost' => 'text-primary-600 hover:bg-primary-50',
        default => 'bg-primary-600 text-paper hover:bg-primary-700',
    });
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
