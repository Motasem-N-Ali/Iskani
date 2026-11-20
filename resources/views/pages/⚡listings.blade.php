<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()->title(__('pages.listings.title'));
    }
};
?>

<main class="flex flex-1 flex-col gap-8">
    <x-ui.page-header :title="__('pages.listings.title')" :description="__('pages.listings.description')" :phase="3" />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.skeleton-card />
        <x-ui.skeleton-card />
        <x-ui.skeleton-card />
    </div>

    <x-ui.button :href="route('listing', 1)" class="self-start">
        {{ __('pages.listings.cta') }}
        <x-heroicon-o-arrow-right class="size-5 rtl:-scale-x-100" />
    </x-ui.button>
</main>
