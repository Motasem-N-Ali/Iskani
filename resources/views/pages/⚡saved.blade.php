<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()->title(__('pages.saved.title'));
    }
};
?>

<main class="flex flex-1 flex-col gap-8">
    <x-ui.page-header :title="__('pages.saved.title')" :description="__('pages.saved.description')" :phase="3" />

    <x-ui.button :href="route('listings')" class="self-start">
        {{ __('pages.saved.cta') }}
        <x-heroicon-o-arrow-right class="size-5 rtl:-scale-x-100" />
    </x-ui.button>
</main>
