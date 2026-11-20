<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<main class="flex min-h-dvh flex-col items-center justify-center gap-6 px-4 text-center">
    <h1 class="font-serif text-[clamp(2rem,6vw,4rem)] leading-tight font-bold text-primary-950">
        {{ __('home.headline') }}
    </h1>

    <p class="max-w-prose font-serif text-lg text-steel-600">
        {{ __('home.subtitle') }}
    </p>

    {{-- ms-* / ps-* are logical (start-side) utilities; the arrow flips itself in RTL. --}}
    <button type="button"
        class="inline-flex h-14 items-center rounded-xl bg-primary-600 ps-8 pe-6 text-lg font-semibold text-paper transition-colors hover:bg-primary-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
        {{ __('home.cta') }}
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" class="ms-3 size-5 rtl:-scale-x-100">
            <path d="M5 12h14M13 6l6 6-6 6" />
        </svg>
    </button>
</main>
