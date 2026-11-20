<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

        {{-- Only the fonts this language uses: Cairo for Arabic, Montserrat + Lora for English. --}}
        @fonts(app()->getLocale() === 'ar' ? ['cairo'] : ['montserrat', 'lora'])

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body>
        @php
            // Landlord pages get the landlord menu, every other page gets the site menu.
            $menuSection = request()->routeIs('landlord.*') ? 'landlord' : 'site';
            $menuItems = collect(config("menu.$menuSection"))
                ->filter(fn ($item) => \Illuminate\Support\Facades\Route::has($item['route']))
                ->values();
        @endphp

        <x-ui.menu :items="$menuItems" />

        {{-- There is no navbar. Every page brings its own <main>. The top padding keeps content clear of the burger button. --}}
        <div class="mx-auto flex min-h-dvh w-full max-w-5xl flex-col px-4 pt-20 pb-10">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
