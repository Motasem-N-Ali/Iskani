<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Throwaway page to prove Livewire works (step 4). Removed after step 5.
Route::livewire('/livewire-test', 'counter');
