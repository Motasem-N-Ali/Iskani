@props(['label', 'name' => 'phone', 'prefixes' => ['+970', '+972']])

<div x-data="{ prefix: @js($prefixes[0]), digits: '' }">
    <label for="{{ $name }}-digits" class="mb-2 block text-lg font-semibold text-content-soft">{{ $label }}</label>

    {{-- Numbers always read left to right, also on an Arabic page: the prefix first, then the digits. --}}
    <div class="flex gap-2" dir="ltr">
        <div class="relative">
            <select id="{{ $name }}-prefix" x-model="prefix" aria-label="{{ __('ui.phone_prefix') }}"
                class="h-14 w-28 appearance-none rounded-xl border-2 border-accent bg-surface ps-4 pe-10 text-lg text-content focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                @foreach ($prefixes as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
            <x-heroicon-o-chevron-down class="pointer-events-none absolute end-3 top-1/2 size-5 -translate-y-1/2 text-accent" />
        </div>

        <input id="{{ $name }}-digits" type="tel" inputmode="numeric" autocomplete="tel-national" maxlength="9"
            x-model="digits" x-on:input="digits = digits.replace(/\D/g, '').replace(/^0+/, '')"
            {{ $attributes->class('h-14 min-w-0 flex-1 rounded-xl border-2 border-accent bg-surface px-4 text-lg text-content focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent') }}>
    </div>

    {{-- The server receives the full international number, for example +970599123456. --}}
    <input type="hidden" name="{{ $name }}" x-bind:value="digits ? prefix + digits : ''">
</div>
