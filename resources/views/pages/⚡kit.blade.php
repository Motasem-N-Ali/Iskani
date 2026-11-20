<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()->title('Kit');
    }
};
?>

{{--
    Developer-only page (never registered in production). It shows every brand piece in one place.
    Its labels are plain English on purpose; the samples use the real translation files.
--}}
@php
    // Class names are written out in full so Tailwind can see and generate them.
    $swatches = [
        'Brand blue (primary)' => [
            'primary-50' => 'bg-primary-50', 'primary-100' => 'bg-primary-100', 'primary-200' => 'bg-primary-200',
            'primary-300' => 'bg-primary-300', 'primary-400' => 'bg-primary-400', 'primary-500' => 'bg-primary-500',
            'primary-600 (brand)' => 'bg-primary-600', 'primary-700' => 'bg-primary-700', 'primary-800' => 'bg-primary-800',
            'primary-900' => 'bg-primary-900', 'primary-950' => 'bg-primary-950',
        ],
        'Neutrals' => [
            'ink' => 'bg-ink', 'paper' => 'bg-paper', 'steel-800' => 'bg-steel-800', 'steel-600' => 'bg-steel-600',
            'steel-400' => 'bg-steel-400', 'mist-100' => 'bg-mist-100', 'mist-50' => 'bg-mist-50',
        ],
        'Support and status' => [
            'support-cyan' => 'bg-support-cyan', 'support-violet' => 'bg-support-violet', 'success' => 'bg-success',
            'warning' => 'bg-warning', 'danger' => 'bg-danger', 'info' => 'bg-info',
        ],
    ];
@endphp

<main class="flex flex-1 flex-col gap-14">
    <header class="space-y-3">
        <p class="inline-block rounded-full bg-primary-100 px-3 py-1 text-sm font-semibold text-primary-800">
            Developer only · locale: {{ app()->getLocale() }} · dir: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}
        </p>
        <h1 class="font-serif text-[clamp(1.75rem,5vw,3rem)] leading-tight font-bold text-primary-950">Kit</h1>
        <p class="max-w-prose font-serif text-lg text-steel-600">
            Every brand piece on one page. Switch the language in the menu (the burger button) to check Arabic with Cairo and English with Montserrat and Lora.
        </p>
    </header>

    <section class="space-y-6">
        <h2 class="text-2xl font-bold text-primary-950">Colors</h2>
        @foreach ($swatches as $group => $items)
            <div>
                <h3 class="mb-3 font-semibold text-steel-800">{{ $group }}</h3>
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6">
                    @foreach ($items as $name => $class)
                        <div>
                            <div class="h-12 rounded-lg ring-1 ring-mist-100 {{ $class }}"></div>
                            <p class="mt-1 text-xs text-steel-600">{{ $name }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>

    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-primary-950">Typography</h2>
        <p class="text-sm text-steel-600">font-serif (headlines and long text) and font-sans (interface). In Arabic both are Cairo.</p>
        <h3 class="font-serif text-[clamp(2rem,6vw,4rem)] leading-tight font-bold text-primary-950">{{ __('home.headline') }}</h3>
        <p class="max-w-prose font-serif text-lg text-steel-600">{{ __('home.subtitle') }}</p>
        <p class="font-sans text-lg font-semibold text-steel-800">{{ __('pages.listing.locked_address') }} · {{ __('pages.listing.locked_map') }} · 1200 ₪</p>
        <p class="font-sans text-sm text-steel-600">0123456789 · {{ __('pages.me.description') }}</p>
    </section>

    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-primary-950">Buttons</h2>
        <div class="flex flex-wrap gap-4">
            <x-ui.button>Primary</x-ui.button>
            <x-ui.button variant="secondary">Secondary</x-ui.button>
            <x-ui.button variant="ghost">Ghost</x-ui.button>
            <x-ui.button disabled>Disabled</x-ui.button>
            <x-ui.button>
                {{ __('home.cta') }}
                <x-heroicon-o-arrow-right class="size-5 rtl:-scale-x-100" />
            </x-ui.button>
        </div>
    </section>

    <section class="space-y-6">
        <h2 class="text-2xl font-bold text-primary-950">Fields</h2>
        <div class="grid max-w-md gap-6">
            <x-ui.input label="Text field" name="kit_text" placeholder="…" />
            <x-ui.input label="Field with an error" name="kit_error" error="This is how an error looks." />
            <x-ui.select label="Select" name="kit_select">
                <option>Ramallah</option>
                <option>Al-Bireh</option>
                <option>Beitunia</option>
            </x-ui.select>
            <x-ui.phone-input label="Phone: prefix first, then the digits" name="kit_phone" />
        </div>
    </section>

    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-primary-950">Cards and placeholders</h2>
        <div class="grid gap-4 sm:grid-cols-3">
            <x-ui.card>
                <p class="font-semibold text-steel-800">Card</p>
                <p class="text-steel-600">A plain container.</p>
            </x-ui.card>
            <x-ui.skeleton-card />
            <x-ui.locked-block label="Locked block" />
        </div>
    </section>

    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-primary-950">Icons (Heroicons)</h2>
        <div class="flex flex-wrap items-center gap-6 text-primary-600">
            <x-heroicon-o-home class="size-8" />
            <x-heroicon-o-map-pin class="size-8" />
            <x-heroicon-o-banknotes class="size-8" />
            <x-heroicon-o-bolt class="size-8" />
            <x-heroicon-o-wifi class="size-8" />
            <x-heroicon-o-key class="size-8" />
            <x-heroicon-o-bookmark class="size-8" />
            <x-heroicon-o-bell class="size-8" />
            <x-heroicon-s-heart class="size-8" />
        </div>
        <p class="flex items-center gap-3 text-steel-800">
            <x-heroicon-o-arrow-right class="size-6 text-primary-600 rtl:-scale-x-100" />
            A direction arrow flips in Arabic.
            <x-heroicon-o-lock-closed class="size-6 text-primary-600" />
            Other icons do not.
        </p>
    </section>

    <section class="space-y-4">
        <h2 class="text-2xl font-bold text-primary-950">Bottom sheet and menu</h2>
        <p class="max-w-prose text-steel-600">The burger button (top corner) opens the full-page menu. The button below opens the bottom sheet.</p>
        <x-ui.button x-data="{}" x-on:click="$dispatch('open-sheet', 'kit')" variant="secondary" class="self-start">Open the sheet</x-ui.button>

        <x-ui.sheet name="kit" title="Bottom sheet">
            <p class="mb-4 text-steel-600">Esc, the X, or a tap outside closes it. Focus stays inside while it is open.</p>
            <x-ui.phone-input label="Phone" name="kit_sheet_phone" />
        </x-ui.sheet>
    </section>
</main>
