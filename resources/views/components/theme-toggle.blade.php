<span x-data {{ $attributes }}>
    <flux:button variant="subtle" square size="sm" icon="sun"
        x-show="$flux.appearance !== 'dark'"
        x-on:click="$flux.appearance = 'dark'" />
    <flux:button variant="subtle" square size="sm" icon="moon"
        x-show="$flux.appearance === 'dark'"
        x-on:click="$flux.appearance = 'light'" />
</span>
