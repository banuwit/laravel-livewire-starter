@props([
    'name',
    'icon' => null,
    'iconTrailing' => null,
])

<button
    type="button"
    role="tab"
    data-tab-name="{{ $name }}"
    @click="tab = @js($name)"
    :aria-selected="tab === @js($name)"
    :class="{
        // Default (underline)
        'inline-flex items-center gap-2 px-1 py-2 -mb-px font-medium border-b-2 transition cursor-pointer': variant === 'default',
        'border-zinc-900 dark:border-white text-zinc-900 dark:text-white': variant === 'default' && tab === @js($name),
        'border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-white': variant === 'default' && tab !== @js($name),

        // Segmented
        'inline-flex items-center gap-2 px-3 py-1.5 font-medium rounded-md transition cursor-pointer': variant === 'segmented',
        'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm': variant === 'segmented' && tab === @js($name),
        'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white': variant === 'segmented' && tab !== @js($name),

        // Pills
        'inline-flex items-center gap-2 px-3 py-1.5 font-medium rounded-full transition cursor-pointer': variant === 'pills',
        'bg-zinc-900 dark:bg-white text-white dark:text-zinc-900': variant === 'pills' && tab === @js($name),
        'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/10': variant === 'pills' && tab !== @js($name),
    }"
    {{ $attributes }}
>
    @if ($icon)
        <flux:icon :name="$icon" variant="mini" />
    @endif
    <span>{{ $slot }}</span>
    @if ($iconTrailing)
        <flux:icon :name="$iconTrailing" variant="mini" />
    @endif
</button>
