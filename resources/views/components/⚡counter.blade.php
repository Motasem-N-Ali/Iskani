<?php

use Livewire\Component;

new class extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }
};
?>

<div>
    <p>Count: <span data-testid="count">{{ $count }}</span></p>
    <button type="button" wire:click="increment">+1</button>
</div>
