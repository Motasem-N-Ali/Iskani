@props(['label', 'name', 'type' => 'text', 'error' => null])

<div>
    <label for="{{ $name }}" class="mb-2 block text-lg font-semibold text-content-soft">{{ $label }}</label>

    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
        @if ($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{ $attributes->class('h-14 w-full rounded-xl border-2 border-accent bg-surface px-4 text-lg text-content placeholder:text-content-muted focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent') }}>

    @if ($error)
        <p id="{{ $name }}-error" class="mt-2 flex items-center gap-2 text-content-soft">
            <x-heroicon-o-exclamation-circle class="size-5 shrink-0 text-danger" />
            {{ $error }}
        </p>
    @endif
</div>
