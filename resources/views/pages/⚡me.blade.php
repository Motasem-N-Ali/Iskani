<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()->title(__('pages.me.title'));
    }
};
?>

<main class="flex flex-1 flex-col gap-8">
    <x-ui.page-header :title="__('pages.me.title')" :description="__('pages.me.description')" :phase="3" />

    <x-ui.button :href="route('home')" variant="secondary" class="self-start">
        <x-heroicon-o-arrow-left class="size-5 rtl:-scale-x-100" />
        {{ __('pages.me.cta') }}
    </x-ui.button>
</main>
