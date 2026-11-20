@props(['items'])

{{--
    The only navigation: a burger button that opens a full-page menu from the side, and an X in the
    same spot that closes it. There is no navbar. What is in the menu comes from config/menu.php.

    Under the list sit two toggles: the language (Arabic / English) and the theme (light / dark).
--}}
@php
    // Every row in the menu looks the same: centered. On hover or focus a color band grows out from the
    // middle and fades toward both sides, instead of filling the whole width. It is a ::before layer
    // (so it can animate). The gradient is symmetric, so it works the same in Arabic (RTL) and English (LTR).
    $row = 'group relative isolate flex h-16 w-full items-center justify-center gap-4 text-xl font-semibold focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-paper before:absolute before:inset-0 before:-z-10 before:origin-center before:scale-x-50 before:bg-linear-to-r before:from-transparent before:via-menu-hover before:to-transparent before:opacity-0 before:transition before:duration-300 before:ease-out hover:before:scale-x-100 hover:before:opacity-100 focus-visible:before:scale-x-100 focus-visible:before:opacity-100 motion-reduce:before:transition-none';
    $rowActive = 'before:scale-x-100 before:opacity-100';
    // Icons stay still. Only the row's color band reacts to hover (see the design rule in CLAUDE.md).
    $icon = 'size-7 shrink-0';

    // A toggle: a pill with two segments. The chosen one is filled, the other is outlined text.
    $segment = 'flex h-11 items-center justify-center gap-2 rounded-full px-5 text-base font-semibold transition-colors duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-paper';
    $segmentOn = 'bg-paper text-ink';
    $segmentOff = 'text-paper hover:bg-paper/15';
@endphp

<div x-data="{
        open: false,
        theme: document.documentElement.dataset.theme || 'light',
        setTheme(value) {
            this.theme = value;
            document.documentElement.dataset.theme = value;
            try { localStorage.setItem('iskani.theme', value); } catch (e) {}
        },
    }" x-on:keydown.escape.window="open = false">
    <button type="button" x-on:click="open = true" x-bind:aria-expanded="open" aria-controls="site-menu"
        aria-label="{{ __('menu.open') }}"
        class="fixed start-3 top-3 z-30 flex size-16 items-center justify-center text-content focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-content">
        <x-heroicon-o-bars-3 class="size-11" />
    </button>

    {{-- It slides in from the start side: the right in Arabic, the left in English. --}}
    <nav id="site-menu" x-show="open" x-cloak x-trap.noscroll="open" aria-label="{{ __('menu.open') }}"
        x-transition:enter="transition duration-200 ease-out motion-reduce:duration-0"
        x-transition:enter-start="ltr:-translate-x-full rtl:translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition duration-150 ease-in motion-reduce:duration-0"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="ltr:-translate-x-full rtl:translate-x-full"
        class="fixed inset-0 z-50 flex flex-col overflow-y-auto bg-menu text-paper">
        <button type="button" x-on:click="open = false" aria-label="{{ __('menu.close') }}"
            class="absolute start-3 top-3 flex size-16 items-center justify-center text-paper focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-paper">
            <x-heroicon-o-x-mark class="size-11" />
        </button>

        {{-- The light logo, always at the bottom center of the open menu. --}}
        <img src="{{ asset('images/logo/logo-light.svg') }}" alt="{{ config('app.name') }}"
            class="pointer-events-none fixed bottom-14 left-1/2 h-20 w-auto -translate-x-1/2">

        {{-- my-auto centers the list in the screen, both ways. The list is narrow so the fade has room. The bottom padding keeps it clear of the logo. --}}
        <div class="mx-auto my-auto w-full max-w-md px-4 pt-24 pb-48">
            <ul class="flex flex-col gap-2">
                @foreach ($items as $item)
                    @php($active = request()->routeIs($item['route']) && empty($item['params']))
                    <li class="animate-rise" style="animation-delay: {{ 100 + $loop->index * 55 }}ms">
                        <a href="{{ route($item['route'], $item['params'] ?? []) }}" @if ($active) aria-current="page" @endif
                            class="{{ $row }} {{ $active ? $rowActive : '' }}">
                            {{ svg($item['icon'], $icon.(! empty($item['flip']) ? ' rtl:-scale-x-100' : '')) }}
                            {{ __('menu.items.'.$item['key']) }}
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- The two toggles, under the list. --}}
            <div class="mt-8 flex animate-rise flex-col items-center gap-4" style="animation-delay: {{ 100 + count($items) * 55 }}ms">
                <form method="POST" action="{{ route('locale') }}">
                    @csrf
                    <div role="group" aria-label="{{ __('menu.language') }}" class="flex gap-1 rounded-full border border-paper/40 p-1">
                        {{-- Language names are written in their own language on purpose. --}}
                        @foreach (['ar' => 'العربية', 'en' => 'English'] as $code => $languageName)
                            @php($current = app()->getLocale() === $code)
                            <button type="submit" name="locale" value="{{ $code }}" lang="{{ $code }}"
                                @if ($current) aria-pressed="true" @else aria-pressed="false" @endif
                                class="{{ $segment }} {{ $current ? $segmentOn : $segmentOff }}">
                                {{ $languageName }}
                            </button>
                        @endforeach
                    </div>
                </form>

                <div role="group" aria-label="{{ __('menu.theme.label') }}" class="flex gap-1 rounded-full border border-paper/40 p-1">
                    <button type="button" x-on:click="setTheme('light')" x-bind:aria-pressed="theme === 'light'"
                        x-bind:class="theme === 'light' ? '{{ $segmentOn }}' : '{{ $segmentOff }}'" class="{{ $segment }}">
                        <x-heroicon-o-sun class="size-5" />
                        {{ __('menu.theme.light') }}
                    </button>
                    <button type="button" x-on:click="setTheme('dark')" x-bind:aria-pressed="theme === 'dark'"
                        x-bind:class="theme === 'dark' ? '{{ $segmentOn }}' : '{{ $segmentOff }}'" class="{{ $segment }}">
                        <x-heroicon-o-moon class="size-5" />
                        {{ __('menu.theme.dark') }}
                    </button>
                </div>
            </div>
        </div>
    </nav>
</div>
