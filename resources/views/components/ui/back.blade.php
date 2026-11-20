@props(['href' => null])

{{--
    The back control: a curved arrow with the word "Back" under it (Arabic: "رجوع"), fixed under the burger
    button and lined up with its center. It never says where it goes. Use it on a page that sits one level
    below another: with `href` for a link, or with `wire:click` for a step inside a page.
    A back control goes inside the menu only if the menu ever has a nested list. Today it does not.
--}}
@php
    $classes = 'fixed start-3 top-20 z-30 flex w-16 flex-col items-center gap-0.5 py-1 text-content transition-colors duration-200 hover:text-accent focus-visible:text-accent focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        <x-heroicon-o-arrow-uturn-left class="size-7 rtl:-scale-x-100" />
        <span class="text-xs font-semibold">{{ __('ui.back') }}</span>
    </a>
@else
    <button type="button" {{ $attributes->class($classes) }}>
        <x-heroicon-o-arrow-uturn-left class="size-7 rtl:-scale-x-100" />
        <span class="text-xs font-semibold">{{ __('ui.back') }}</span>
    </button>
@endif
