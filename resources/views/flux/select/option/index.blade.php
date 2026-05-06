@blaze(fold: true, safe: ['filterable', 'indicator', 'loading'])

@aware(['variant', 'indicator', 'multiple'])

@props([
    'value'     => null,
    'label'     => null,
    'disabled'  => false,
    'variant'   => 'default',
    'indicator' => 'check',
    'multiple'  => false,
])

@php
$isCustomVariant = $variant !== 'default' && Flux::componentExists('select.variants.' . $variant);

if (!$isCustomVariant) {
    // Native <option> for default variant
    echo '<option ' . $attributes . ($value !== null ? ' value="' . e($value) . '"' : '') . '>' . $slot . '</option>';
    return;
}

$labelText = $label ?? strip_tags((string) $slot);

// Custom option for listbox / combobox
$optionClasses = Flux::classes()
    ->add('relative flex items-center w-full gap-2 px-3 py-2 text-sm cursor-default select-none')
    ->add('text-zinc-800 dark:text-white')
    ->add('hover:bg-zinc-50 dark:hover:bg-zinc-600')
    ->add($disabled ? 'opacity-50 pointer-events-none' : '')
    ;

$valueStr = e($value ?? '');
$labelJson = json_encode($labelText);
@endphp

@if ($isCustomVariant)
    <div
        role="option"
        data-flux-select-option
        data-value="{{ $valueStr }}"
        data-label="{{ $labelText }}"
        {{ $attributes->except(['value', 'label', 'indicator', 'multiple', 'disabled']) }}
        x-on:click="selectValue('{{ $valueStr }}')"
        {{-- [BUG FIX] Use x-bind:class for active/selected bg instead of Tailwind data-* variant --}}
        x-bind:class="isSelected('{{ $valueStr }}') ? 'bg-accent/10 dark:bg-accent/20' : ''"
        x-show="filterOption({{ $labelJson }})"
        class="{{ $optionClasses }}"
        @if ($disabled) aria-disabled="true" @endif
    >
        {{-- Check indicator (indicator inherited via @aware or default) --}}
        @if ($indicator === 'checkbox')
            <span
                class="size-4 shrink-0 rounded border flex items-center justify-center transition-colors"
                x-bind:class="isSelected('{{ $valueStr }}')
                    ? 'bg-accent border-accent'
                    : 'bg-white dark:bg-zinc-700 border-zinc-300 dark:border-zinc-500'"
            >
                <svg
                    x-show="isSelected('{{ $valueStr }}')"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="size-3 text-white"
                >
                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                </svg>
            </span>
        @elseif ($indicator === 'radio')
            <span
                class="size-4 shrink-0 rounded-full border flex items-center justify-center transition-colors"
                x-bind:class="isSelected('{{ $valueStr }}')
                    ? 'bg-accent border-accent'
                    : 'bg-white dark:bg-zinc-700 border-zinc-300 dark:border-zinc-500'"
            >
                <svg
                    x-show="isSelected('{{ $valueStr }}')"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="size-2 text-white"
                >
                    <circle cx="10" cy="10" r="6" />
                </svg>
            </span>
        @else
            {{-- Default: check icon, always reserves space to prevent layout shift --}}
            <span class="size-4 shrink-0 flex items-center justify-center text-accent">
                <svg
                    x-show="isSelected('{{ $valueStr }}')"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                    class="size-4"
                    style="display:none;"
                >
                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                </svg>
            </span>
        @endif

        <span class="flex-1">{{ $slot }}</span>
    </div>
@endif
