@props([])

<div
    x-data="{
        open: false,
        x: 0,
        y: 0,
        show(e) {
            e.preventDefault();
            this.x = Math.min(e.clientX, window.innerWidth  - 180);
            this.y = Math.min(e.clientY, window.innerHeight - 200);
            this.open = true;
        },
        hide() { this.open = false; }
    }"
    @contextmenu.prevent="show($event)"
    @click.outside="hide()"
    @keydown.escape.window="hide()"
    class="relative"
    {{ $attributes }}
>
    {{ $slot }}

    <template x-teleport="body">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-75"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-50"
            x-transition:leave-end="opacity-0 scale-95"
            :style="`position: fixed; top: ${y}px; left: ${x}px; z-index: 9999`"
            class="min-w-44 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 rounded-xl shadow-lg py-1"
            @click="hide()"
        >
            {{ $menu ?? '' }}
        </div>
    </template>
</div>
