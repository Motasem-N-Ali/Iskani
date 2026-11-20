<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()->title(__('pages.landlord.create.title'));
    }
};
?>

<main class="flex flex-1 flex-col gap-8 pt-16">
    <x-ui.back :href="route('landlord.housings')" />

    <x-ui.page-header :title="__('pages.landlord.create.title')" :description="__('pages.landlord.create.description')" :phase="2" />

    <x-ui.button :href="route('landlord.housings')" variant="secondary" class="self-start">
        <x-heroicon-o-arrow-left class="size-5 rtl:-scale-x-100" />
        {{ __('pages.landlord.create.cta') }}
    </x-ui.button>
</main>
