@props(['items'])

{{--
    The only navigation: a burger button that opens a full-page menu from the side, and an X in the
    same spot that closes it. There is no navbar. What is in the menu comes from config/menu.php.
--}}
<div x-data="{ open: false }" x-on:keydown.escape.window="open = false">
    <button type="button" x-on:click="open = true" x-bind:aria-expanded="open" aria-controls="site-menu"
        aria-label="{{ __('menu.open') }}"
        class="fixed start-4 top-4 z-30 flex size-12 items-center justify-center rounded-full bg-paper text-primary-600 shadow-md ring-1 ring-mist-100 transition-colors hover:bg-primary-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
        <x-heroicon-o-bars-3 class="size-7" />
    </button>

    {{-- It slides in from the start side: the right in Arabic, the left in English. --}}
    <nav id="site-menu" x-show="open" x-cloak x-trap.noscroll="open" aria-label="{{ __('menu.open') }}"
        x-transition:enter="transition duration-200 ease-out motion-reduce:duration-0"
        x-transition:enter-start="ltr:-translate-x-full rtl:translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition duration-150 ease-in motion-reduce:duration-0"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="ltr:-translate-x-full rtl:translate-x-full"
        class="fixed inset-0 z-50 flex flex-col overflow-y-auto bg-primary-600 text-paper">
        <button type="button" x-on:click="open = false" aria-label="{{ __('menu.close') }}"
            class="absolute start-4 top-4 flex size-12 items-center justify-center rounded-full text-paper transition-colors hover:bg-primary-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-paper">
            <x-heroicon-o-x-mark class="size-7" />
        </button>

        <ul class="mt-24 flex flex-col gap-2 px-4">
            @foreach ($items as $item)
                @php($active = request()->routeIs($item['route']))
                <li>
                    <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                        class="flex h-16 items-center gap-4 rounded-xl px-4 text-xl font-semibold transition-colors hover:bg-primary-700 {{ $active ? 'bg-primary-700' : '' }}">
                        {{ svg($item['icon'], 'size-7 shrink-0') }}
                        {{ __('menu.items.'.$item['key']) }}
                    </a>
                </li>
            @endforeach
        </ul>

        <form method="POST" action="{{ route('locale') }}" class="mt-auto border-t border-primary-500 p-6">
            @csrf
            <p class="mb-3 flex items-center gap-2 font-semibold">
                <x-heroicon-o-language class="size-5" />
                {{ __('menu.language') }}
            </p>
            <div class="flex gap-3">
                {{-- Language names are written in their own language on purpose. --}}
                @foreach (['ar' => 'العربية', 'en' => 'English'] as $code => $languageName)
                    @php($current = app()->getLocale() === $code)
                    <button type="submit" name="locale" value="{{ $code }}" lang="{{ $code }}"
                        @if ($current) aria-current="true" @endif
                        class="h-14 flex-1 rounded-xl border-2 border-paper text-lg font-semibold transition-colors {{ $current ? 'bg-paper text-primary-600' : 'text-paper hover:bg-primary-700' }}">
                        {{ $languageName }}
                    </button>
                @endforeach
            </div>
        </form>
    </nav>
</div>
