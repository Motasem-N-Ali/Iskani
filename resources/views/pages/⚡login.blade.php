<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()->title(__('pages.login.title'));
    }
};
?>

<main class="flex flex-1 flex-col gap-8">
    <x-ui.page-header :title="__('pages.login.title')" :description="__('pages.login.description')" :phase="1" />

    <div class="max-w-md space-y-4">
        <x-ui.phone-input :label="__('pages.login.phone')" />
        <x-ui.button class="w-full">{{ __('pages.login.send_code') }}</x-ui.button>
    </div>
</main>
