<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()->title(__('pages.landlord.dashboard.title'));
    }
};
?>

<main class="flex flex-1 flex-col gap-8 pt-16">
    <x-ui.back :href="route('home')" />

    <x-ui.page-header :title="__('pages.landlord.dashboard.title')" :description="__('pages.landlord.dashboard.description')" :phase="2" />

    <x-ui.button :href="route('landlord.housings.create')" class="self-start">
        <x-heroicon-o-plus-circle class="size-6" />
        {{ __('pages.landlord.dashboard.cta') }}
    </x-ui.button>
</main>
