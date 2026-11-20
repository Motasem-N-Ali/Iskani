@props(['title', 'description' => null, 'phase' => null])

<header class="space-y-3">
    @if ($phase)
        <p class="inline-block rounded-full bg-primary-100 px-3 py-1 text-sm font-semibold text-primary-800">
            {{ __('pages.placeholder', ['phase' => $phase]) }}
        </p>
    @endif

    <h1 class="font-serif text-[clamp(1.75rem,5vw,3rem)] leading-tight font-bold text-primary-950">{{ $title }}</h1>

    @if ($description)
        <p class="max-w-prose font-serif text-lg text-steel-600">{{ $description }}</p>
    @endif
</header>
