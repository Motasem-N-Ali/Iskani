@props(['title', 'description' => null, 'phase' => null])

<header class="space-y-3">
    @if ($phase)
        <p class="inline-block rounded-full bg-tint px-3 py-1 text-sm font-semibold text-accent">
            {{ __('pages.placeholder', ['phase' => $phase]) }}
        </p>
    @endif

    <h1 class="font-serif text-[clamp(1.75rem,5vw,3rem)] leading-tight font-bold text-content-strong">{{ $title }}</h1>

    @if ($description)
        <p class="max-w-prose font-serif text-lg text-content-muted">{{ $description }}</p>
    @endif
</header>
