{{-- The legacy @component call renders the shared layout and passes the content in as $slot. --}}
@component('layouts.app', ['title' => __('pages.not_found.title')])
    <main class="flex flex-1 flex-col items-center justify-center gap-6 text-center">
        <p class="font-serif text-[clamp(4rem,15vw,8rem)] leading-none font-bold text-line-brand" aria-hidden="true">404</p>

        <h1 class="font-serif text-[clamp(1.75rem,5vw,3rem)] leading-tight font-bold text-content-strong">
            {{ __('pages.not_found.title') }}
        </h1>

        <p class="max-w-prose font-serif text-lg text-content-muted">{{ __('pages.not_found.description') }}</p>

        <x-ui.button :href="url('/')">
            {{ __('pages.not_found.cta') }}
        </x-ui.button>
    </main>
@endcomponent
