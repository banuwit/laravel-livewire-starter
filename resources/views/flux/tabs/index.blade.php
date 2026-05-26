@props(['variant' => 'default', 'size' => 'base', 'scrollable' => false, 'default' => null])

@php
$wireModel = $attributes->wire('model');
$property  = $wireModel?->value ?? null;

// Only manage own Alpine state when wire:model or default is given.
// When inside flux:tab.group, the group already provides { tab, variant }.
$needsOwnState = $property !== null || $default !== null;

$excludedAttrs = array_filter([
    'wire:model',
    'wire:model.live',
    'wire:model.defer',
    'wire:model.lazy',
    $wireModel?->directive,
]);

$classes = Flux::classes()
    ->add(match ($variant) {
        'segmented' => 'inline-flex p-1 gap-1 rounded-lg bg-zinc-100 dark:bg-zinc-700',
        'pills'     => 'flex flex-wrap gap-1',
        default     => 'flex gap-4 border-b border-zinc-200 dark:border-zinc-700',
    })
    ->add($scrollable ? 'overflow-x-auto' : '');
@endphp

@if ($needsOwnState)
<div
    x-data="{
        variant: @js($variant),
        tab: @js($default ?? ''),
        init() {
            @if($property)
            if (typeof $wire !== 'undefined') {
                const val = $wire[@js($property)];
                if (val !== undefined) this.tab = val;
                this.$watch('tab', v => $wire.set(@js($property), v));
            }
            @else
            this.$nextTick(() => {
                if (!this.tab) {
                    const first = this.$el.querySelector('[data-tab-name]');
                    if (first) this.tab = first.dataset.tabName;
                }
            });
            @endif
        },
    }"
    {{ $attributes->except($excludedAttrs)->class($classes) }}
    data-flux-tabs
>
    {{ $slot }}
</div>
@else
<div
    role="tablist"
    {{ $attributes->class($classes) }}
    data-flux-tabs
>
    {{ $slot }}
</div>
@endif
