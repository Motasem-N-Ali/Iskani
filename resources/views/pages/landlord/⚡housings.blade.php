<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()->title(__('pages.landlord.housings.title'));
    }
};
?>

<main class="flex flex-1 flex-col gap-8 pt-16">
    <x-ui.back :href="route('landlord.dashboard')" />

    <x-ui.page-header :title="__('pages.landlord.housings.title')" :description="__('pages.landlord.housings.description')" :phase="2" />

    <div class="grid gap-4 sm:grid-cols-2">
        <x-ui.skeleton-card />
        <x-ui.skeleton-card />
    </div>

    <x-ui.button :href="route('landlord.housings.create')" class="self-start">
        <x-heroicon-o-plus-circle class="size-6" />
        {{ __('pages.landlord.housings.cta') }}
    </x-ui.button>
</main>
