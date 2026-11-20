<?php

namespace Tests\Feature;

use Livewire\Livewire;
use Tests\TestCase;

class CounterTest extends TestCase
{
    public function test_counter_starts_at_zero_and_increments(): void
    {
        Livewire::test('counter')
            ->assertSet('count', 0)
            ->call('increment')
            ->assertSet('count', 1)
            ->call('increment')
            ->assertSet('count', 2);
    }
}
