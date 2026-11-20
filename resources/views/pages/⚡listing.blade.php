<?php

use Livewire\Component;

new class extends Component
{
    public int $housingId;

    public function mount(int $id): void
    {
        $this->housingId = $id;
    }

    public function render()
    {
        return $this->view()->title(__('pages.listing.title'));
    }
};
?>

<main class="flex flex-1 flex-col gap-8">
    <x-ui.page-header :title="__('pages.listing.title')" :description="__('pages.listing.description')" :phase="3" />

    {{-- Photos, price and area are public. Address, map and contact stay locked until login. --}}
    <div class="aspect-video rounded-2xl bg-mist-100" role="img" aria-label="{{ __('pages.listing.photos') }}"></div>

    <div class="grid gap-3 sm:grid-cols-3">
        <x-ui.locked-block :label="__('pages.listing.locked_address')" />
        <x-ui.locked-block :label="__('pages.listing.locked_map')" />
        <x-ui.locked-block :label="__('pages.listing.locked_contact')" />
    </div>

    <x-ui.button x-data="{}" x-on:click="$dispatch('open-sheet', 'login')" class="self-start">
        {{ __('pages.listing.cta') }}
    </x-ui.button>

    <x-ui.sheet name="login" :title="__('pages.listing.sheet_title')">
        <ul class="mb-6 space-y-3">
            @foreach (__('pages.listing.sheet_unlocks') as $unlock)
                <li class="flex items-center gap-3 text-steel-800">
                    <x-heroicon-o-check-circle class="size-6 shrink-0 text-primary-600" />
                    {{ $unlock }}
                </li>
            @endforeach
        </ul>

        <div class="space-y-4">
            <x-ui.phone-input :label="__('pages.login.phone')" />
            <x-ui.button class="w-full">{{ __('pages.login.send_code') }}</x-ui.button>
        </div>
    </x-ui.sheet>
</main>
