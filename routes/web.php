<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::post('/locale', LocaleController::class)->name('locale');

/*
| Skeleton pages: placeholders that show the shell, menu and brand styling.
| They are never registered in production. When a page is built for real, move it out
| of this block and give it the right login and role checks (Phase 1).
*/
if (! app()->isProduction()) {
    Route::livewire('/listings', 'pages::listings')->name('listings');
    Route::livewire('/listings/{id}', 'pages::listing')->whereNumber('id')->name('listing');
    Route::livewire('/login', 'pages::login')->name('login');
    Route::livewire('/saved', 'pages::saved')->name('saved');
    Route::livewire('/me', 'pages::me')->name('me');

    Route::prefix('landlord')->name('landlord.')->group(function () {
        Route::livewire('/', 'pages::landlord.dashboard')->name('dashboard');
        Route::livewire('/housings', 'pages::landlord.housings')->name('housings');
        Route::livewire('/housings/create', 'pages::landlord.create-housing')->name('housings.create');
    });

    // Developer-only page with every brand piece on one screen.
    Route::livewire('/_kit', 'pages::kit')->name('kit');
}
