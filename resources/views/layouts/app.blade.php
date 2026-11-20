<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="color-scheme" content="light dark">

        {{-- Sets the theme before the first paint, so there is no flash. The saved choice wins, otherwise the
             system setting. It also follows the system while the visitor has not chosen. (DECISIONS.md D15) --}}
        <script>
            (function () {
                var root = document.documentElement, media = window.matchMedia('(prefers-color-scheme: dark)');
                function saved() { try { return localStorage.getItem('iskani.theme'); } catch (e) { return null; } }
                function apply() { var t = saved(); root.dataset.theme = (t === 'light' || t === 'dark') ? t : (media.matches ? 'dark' : 'light'); }
                apply();
                media.addEventListener('change', function () { if (!saved()) apply(); });
            })();
        </script>

        <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

        {{-- Only the fonts this language uses: Cairo for Arabic, Montserrat + Lora for English. --}}
        @fonts(app()->getLocale() === 'ar' ? ['cairo'] : ['montserrat', 'lora'])

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body @class(['overflow-hidden' => $bleed ?? false])>
        @php
            // Landlord pages get the landlord menu, every other page gets the site menu.
            $menuSection = request()->routeIs('landlord.*') ? 'landlord' : 'site';
            $menuItems = collect(config("menu.$menuSection"))
                ->filter(fn ($item) => \Illuminate\Support\Facades\Route::has($item['route']))
                ->values();
        @endphp

        <x-ui.menu :items="$menuItems" />

        {{-- There is no navbar. Every page brings its own <main>. The top padding keeps content clear of the burger button. --}}
        {{-- A page can set `bleed` (through layoutData) to fill exactly one screen and never scroll, like the
             landing page. Every other page scrolls normally. --}}
        <div @class([
            'mx-auto flex w-full flex-col',
            'h-dvh overflow-hidden' => $bleed ?? false,
            'min-h-dvh max-w-5xl px-4 pt-20 pb-10' => ! ($bleed ?? false),
        ])>
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
