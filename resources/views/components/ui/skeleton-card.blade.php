{{-- The loading look of a housing card. Decoration only, so screen readers skip it. --}}
<div aria-hidden="true" {{ $attributes->class('overflow-hidden rounded-2xl border border-line bg-surface shadow-sm motion-safe:animate-pulse') }}>
    <div class="aspect-[4/3] bg-fill-muted"></div>
    <div class="space-y-3 p-4">
        <div class="h-5 w-2/5 rounded bg-fill-muted"></div>
        <div class="h-4 w-4/5 rounded bg-fill-muted"></div>
        <div class="h-4 w-3/5 rounded bg-fill-muted"></div>
    </div>
</div>
