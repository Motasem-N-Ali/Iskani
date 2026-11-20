<?php

use Illuminate\Support\Facades\Route;
use Livewire\Component;

new class extends Component
{
    // The "Start" button goes to the listings page when that page exists.
    public function startUrl(): string
    {
        return Route::has('listings') ? route('listings') : '#';
    }
};
?>

<main class="flex flex-1 flex-col items-center justify-center gap-6 text-center">
    <h1 class="font-serif text-[clamp(2rem,6vw,4rem)] leading-tight font-bold text-primary-950">
        {{ __('home.headline') }}
    </h1>

    <p class="max-w-prose font-serif text-lg text-steel-600">
        {{ __('home.subtitle') }}
    </p>

    {{-- The arrow flips by itself in right-to-left pages. --}}
    <x-ui.button :href="$this->startUrl()" class="ps-8 pe-6">
        {{ __('home.cta') }}
        <x-heroicon-o-arrow-right class="size-5 rtl:-scale-x-100" />
    </x-ui.button>
</main>
