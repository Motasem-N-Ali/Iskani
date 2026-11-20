@props(['name', 'title'])

{{-- A bottom sheet. Open it from anywhere with: x-on:click="$dispatch('open-sheet', 'the-name')" --}}
<div x-data="{ open: false }"
    x-on:open-sheet.window="if ($event.detail === @js($name)) open = true"
    x-on:keydown.escape.window="open = false">
    <div x-show="open" x-cloak class="fixed inset-0 z-40" role="dialog" aria-modal="true"
        aria-labelledby="sheet-{{ $name }}-title">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-primary-950/60" x-on:click="open = false"></div>

        <div x-show="open" x-trap.noscroll="open"
            x-transition:enter="transition duration-200 ease-out motion-reduce:duration-0"
            x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
            x-transition:leave="transition duration-150 ease-in motion-reduce:duration-0"
            x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
            class="absolute inset-x-0 bottom-0 mx-auto max-h-[90dvh] w-full max-w-lg overflow-y-auto rounded-t-3xl bg-surface p-6 shadow-xl">
            <div class="mb-4 flex items-start justify-between gap-4">
                <h2 id="sheet-{{ $name }}-title" class="font-serif text-2xl leading-snug font-bold text-content-strong">{{ $title }}</h2>
                <button type="button" x-on:click="open = false" aria-label="{{ __('ui.close') }}"
                    class="flex size-12 shrink-0 items-center justify-center rounded-full text-content-soft transition-colors hover:bg-fill-muted focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                    <x-heroicon-o-x-mark class="size-6" />
                </button>
            </div>

            {{ $slot }}
        </div>
    </div>
</div>
