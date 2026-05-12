@props([
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'value' => 0,
    'showValue' => false,
    'name' => null,
    'disabled' => false,
])

<div
    x-data="{ value: {{ $value }} }"
    class="w-full"
    {{ $attributes }}
>
    @if($showValue)
        <div class="flex justify-between items-center mb-2">
            <span class="text-xs text-zinc-400">{{ $min }}</span>
            <span x-text="value" class="text-sm font-semibold text-zinc-700 dark:text-zinc-200 tabular-nums"></span>
            <span class="text-xs text-zinc-400">{{ $max }}</span>
        </div>
    @endif

    <input
        type="range"
        min="{{ $min }}"
        max="{{ $max }}"
        step="{{ $step }}"
        x-model="value"
        @input="$dispatch('input', value)"
        @if($name) name="{{ $name }}" @endif
        @disabled($disabled)
        class="w-full h-1.5 appearance-none rounded-full bg-zinc-200 dark:bg-white/15 cursor-pointer
            [&::-webkit-slider-thumb]:appearance-none
            [&::-webkit-slider-thumb]:size-4
            [&::-webkit-slider-thumb]:rounded-full
            [&::-webkit-slider-thumb]:bg-accent
            [&::-webkit-slider-thumb]:cursor-pointer
            [&::-webkit-slider-thumb]:shadow-sm
            [&::-webkit-slider-thumb]:border-2
            [&::-webkit-slider-thumb]:border-white
            [&::-moz-range-thumb]:size-4
            [&::-moz-range-thumb]:rounded-full
            [&::-moz-range-thumb]:bg-accent
            [&::-moz-range-thumb]:border-2
            [&::-moz-range-thumb]:border-white
            disabled:opacity-50 disabled:cursor-not-allowed"
        data-flux-control
    />
</div>
