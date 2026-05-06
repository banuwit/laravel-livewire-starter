@props(['group', 'isChild' => false])

@if (!empty($group['permissions']) && count($group['permissions']) > 0)
    <div
        x-data="{ open: true }"
        class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900/40"
    >
        <flux:checkbox.group wire:model="selectedPermission">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between gap-2 px-3 py-2 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/60 dark:bg-white/5 rounded-t-lg hover:bg-zinc-100 dark:hover:bg-white/10 transition"
                x-bind:class="open ? '' : 'border-b-0 rounded-b-lg'">
                <div class="flex items-center gap-2 min-w-0">
                    <flux:icon.chevron-down variant="mini" class="text-zinc-500 transition-transform shrink-0" x-bind:class="open ? '' : '-rotate-90'" />
                    <flux:heading size="sm" class="font-semibold capitalize truncate">{{ $group['name'] }}</flux:heading>
                </div>
                <span @click.stop>
                    <flux:checkbox.all label="Check All" size="sm" />
                </span>
            </button>

            <div x-show="open" x-collapse>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-2 px-3 py-3">
                    @foreach ($group['permissions'] as $permission)
                        <flux:checkbox
                            value="{{ $permission['id'] }}"
                            :label="str($permission['name'])->after('.')->title()->replace('_', ' ')"
                            size="sm"
                        />
                    @endforeach
                </div>
            </div>
        </flux:checkbox.group>
    </div>
@endif
