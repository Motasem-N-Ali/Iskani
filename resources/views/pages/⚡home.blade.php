<?php

use Illuminate\Support\Facades\Route;
use Livewire\Component;

/*
| The landing page, in steps:
|   0  "Are you a tenant or a landlord?"  two cards
|   1  tenant: choose the city (a dropdown)
|   2  tenant: choose the village (a dropdown), then on to the listings
| A landlord goes straight to the landlord area (it will ask for a login once login exists).
| Nothing is saved here. The visitor's place is remembered only after a real login (DECISIONS.md D14).
| The city and village lists come from config/areas.php for now.
| Livewire keeps the step and the choices on the server, so tests can drive it.
*/
new class extends Component
{
    public int $step = 0;

    public string $role = '';

    public string $city = '';

    public string $village = '';

    protected function rules(): array
    {
        return ['city' => ['required', 'in:'.implode(',', array_keys(config('areas.cities')))]];
    }

    protected function messages(): array
    {
        return ['city.*' => __('home.errors.city')];
    }

    /** Names for the current language, for the select lists. */
    public function cities(): array
    {
        return collect(config('areas.cities'))
            ->map(fn (array $city) => $city['name'][app()->getLocale()])
            ->all();
    }

    public function villages(): array
    {
        return collect(config("areas.cities.{$this->city}.villages", []))
            ->map(fn (array $name) => $name[app()->getLocale()])
            ->all();
    }

    public function cityName(): string
    {
        return config("areas.cities.{$this->city}.name.".app()->getLocale(), '');
    }

    public function chooseRole(string $role)
    {
        if (! in_array($role, ['tenant', 'landlord'], true)) {
            return;
        }

        $this->role = $role;

        if ($role === 'landlord') {
            return $this->redirect(Route::has('landlord.dashboard') ? route('landlord.dashboard') : route('home'));
        }

        $this->step = 1;
    }

    public function next(): void
    {
        $this->validate();

        $this->village = '';
        $this->step = 2;
    }

    public function back(): void
    {
        $this->step = max(0, $this->step - 1);

        if ($this->step === 0) {
            $this->role = '';
            $this->city = '';
        }
    }

    public function search()
    {
        $this->validate();

        // An empty village means "all of the city". The listings page is a skeleton for now.
        $query = array_filter(['city' => $this->city, 'village' => array_key_exists($this->village, $this->villages()) ? $this->village : null]);

        return $this->redirect(Route::has('listings') ? route('listings', $query) : route('home'));
    }

    public function render()
    {
        // The landing fills the whole screen (rings and skyline touch the edges), so it skips the padded container.
        return $this->view()->layoutData(['bleed' => true]);
    }
};
?>

<main class="relative flex h-dvh flex-col items-center overflow-hidden">
    {{-- Decoration only. All three rings are the same size, so they have the same thickness (the stroke is a fixed
         part of the image). Two of them sit up in the corner on the end side (left in Arabic, right in English),
         spread diagonally and pushed past the screen edges so the screen cuts them: the light blue one is cut by
         the top and sits farther from the side, the primary one is cut by the side and sits farther from the top.
         They still loop through each other. The third ring sits in the bottom corner on the start side and is cut
         by the bottom and the side. Everything uses start/end, so switching the language mirrors the picture. --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-0">
        <img src="{{ asset('images/ring-blue-light.svg') }}" alt="" class="absolute -end-10 -top-[6.5rem] size-40 animate-pop md:-end-[4.5rem] md:-top-[11.7rem] md:size-72">
        <img src="{{ asset('images/ring-primary.svg') }}" alt="" class="absolute -end-[6.5rem] -top-12 size-40 animate-pop [animation-delay:120ms] md:-end-[11.7rem] md:-top-[5.4rem] md:size-72">
        <img src="{{ asset('images/ring-primary.svg') }}" alt="" class="absolute -bottom-14 -start-12 size-40 animate-pop [animation-delay:240ms] md:-bottom-[6.3rem] md:-start-[5.4rem] md:size-72">
    </div>

    {{-- The skyline sits on the bottom edge of the screen and spans its full width. --}}
    <img src="{{ asset('images/landing.svg') }}" alt="" aria-hidden="true"
        class="pointer-events-none absolute bottom-0 left-1/2 h-[min(26dvh,16rem)] w-auto max-w-none -translate-x-1/2 animate-lift [animation-delay:200ms] dark:opacity-90">

    {{-- Back: under the burger button, shown from step 1 on. --}}
    @if ($step > 0)
        <x-ui.back wire:click="back" />
    @endif

    {{-- The primary logo on the light theme, the light logo on the dark theme. --}}
    <img src="{{ asset('images/logo/logo-primary.svg') }}" alt="{{ config('app.name') }}" class="relative mt-[min(2.25rem,4.5dvh)] h-[min(6rem,12dvh)] w-auto animate-rise md:h-[min(7rem,13dvh)] dark:hidden">
    <img src="{{ asset('images/logo/logo-light.svg') }}" alt="{{ config('app.name') }}" class="relative mt-[min(2.25rem,4.5dvh)] hidden h-[min(6rem,12dvh)] w-auto animate-rise md:h-[min(7rem,13dvh)] dark:block">

    <div class="relative flex min-h-0 w-full max-w-3xl flex-1 flex-col justify-center gap-[min(1.5rem,3dvh)] px-4 pb-[min(26dvh,16rem)] text-center">
        <h1 wire:key="title-{{ $step }}" id="home-title" class="animate-rise [animation-delay:100ms] font-serif text-[clamp(1.5rem,min(6vw,5.5dvh),3rem)] leading-tight font-bold text-balance text-content-strong">
            @if ($step === 0)
                {{ __('home.role_headline') }}
            @elseif ($step === 1)
                {{ __('home.headline') }}
            @else
                {{ __('home.village_headline', ['city' => $this->cityName()]) }}
            @endif
        </h1>

        @if ($step === 0)
            {{-- Two cards. Hover or focus turns a card primary blue and its text white. The illustration stays in a
                 light panel, so its colors still read on the blue. --}}
            @php
                $card = 'group flex flex-col items-center gap-3 rounded-2xl border-2 border-line-brand bg-surface p-3 pb-[min(1.25rem,2.5dvh)] text-center text-accent transition-colors duration-200 hover:border-primary-600 hover:bg-primary-600 hover:text-paper focus-visible:border-primary-600 focus-visible:bg-primary-600 focus-visible:text-paper focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent';
            @endphp
            <div wire:key="step-0" role="group" aria-labelledby="home-title" class="mx-auto grid w-full max-w-lg animate-rise grid-cols-2 gap-4 [animation-delay:200ms]">
                <button type="button" wire:click="chooseRole('tenant')" class="{{ $card }}">
                    <span class="block w-full rounded-xl bg-primary-50 px-2 py-[min(1rem,2dvh)]"><img src="{{ asset('images/landing/tenant.svg') }}" alt="" class="h-[min(7rem,14dvh)] w-full object-contain"></span>
                    <span class="text-xl font-bold text-content transition-colors duration-200 group-hover:text-paper group-focus-visible:text-paper">{{ __('home.roles.tenant.title') }}</span>
                    <span class="text-sm text-content-muted transition-colors duration-200 group-hover:text-paper group-focus-visible:text-paper">{{ __('home.roles.tenant.hint') }}</span>
                </button>
                <button type="button" wire:click="chooseRole('landlord')" class="{{ $card }}">
                    <span class="block w-full rounded-xl bg-primary-50 px-2 py-[min(1rem,2dvh)]"><img src="{{ asset('images/landing/landlord.svg') }}" alt="" class="h-[min(7rem,14dvh)] w-full object-contain"></span>
                    <span class="text-xl font-bold text-content transition-colors duration-200 group-hover:text-paper group-focus-visible:text-paper">{{ __('home.roles.landlord.title') }}</span>
                    <span class="text-sm text-content-muted transition-colors duration-200 group-hover:text-paper group-focus-visible:text-paper">{{ __('home.roles.landlord.hint') }}</span>
                </button>
            </div>
        @elseif ($step === 1)
            <form wire:key="step-1" wire:submit="next" class="mx-auto w-full max-w-md animate-rise space-y-4 text-start [animation-delay:200ms]">
                <x-ui.select name="city" :label="__('home.city')" wire:model.live="city">
                    <option value="" disabled selected>{{ __('home.city_placeholder') }}</option>
                    @foreach ($this->cities() as $slug => $name)
                        <option value="{{ $slug }}">{{ $name }}</option>
                    @endforeach
                </x-ui.select>
                @error('city')
                    <p class="text-danger" role="alert">{{ $message }}</p>
                @enderror

                <x-ui.button type="submit" class="w-full ps-8 pe-6" :disabled="$city === ''">
                    {{ __('home.continue') }}
                    <x-heroicon-o-arrow-right class="size-5 rtl:-scale-x-100 group-enabled:group-hover:animate-nudge group-enabled:group-focus-visible:animate-nudge" />
                </x-ui.button>
            </form>
        @else
            <form wire:key="step-2" wire:submit="search" class="mx-auto w-full max-w-md animate-rise space-y-4 text-start [animation-delay:200ms]">
                <x-ui.select name="village" :label="__('home.village')" wire:model="village">
                    <option value="">{{ __('home.all_of', ['city' => $this->cityName()]) }}</option>
                    @foreach ($this->villages() as $slug => $name)
                        <option value="{{ $slug }}">{{ $name }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.button type="submit" class="w-full ps-8 pe-6">
                    {{ __('home.show_homes') }}
                    <x-heroicon-o-arrow-right class="size-5 rtl:-scale-x-100 group-enabled:group-hover:animate-nudge group-enabled:group-focus-visible:animate-nudge" />
                </x-ui.button>
            </form>
        @endif
    </div>
</main>
