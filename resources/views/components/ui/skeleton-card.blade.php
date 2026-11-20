{{-- The loading look of a housing card. Decoration only, so screen readers skip it. --}}
<div aria-hidden="true" {{ $attributes->class('overflow-hidden rounded-2xl border border-mist-100 bg-paper shadow-sm motion-safe:animate-pulse') }}>
    <div class="aspect-[4/3] bg-mist-100"></div>
    <div class="space-y-3 p-4">
        <div class="h-5 w-2/5 rounded bg-mist-100"></div>
        <div class="h-4 w-4/5 rounded bg-mist-100"></div>
        <div class="h-4 w-3/5 rounded bg-mist-100"></div>
    </div>
</div>
