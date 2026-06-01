<div x-data="{ open: false }"
     x-on:show-notification-toast.window="
         $flux.toast({
             variant: 'default',
             heading: $event.detail.title,
             text: $event.detail.message
         })
     ">

    {{-- Bell button with unread badge --}}
    <div class="relative">
        <flux:button
            variant="subtle"
            square
            size="sm"
            x-on:click="open = !open"
            aria-label="Notifications"
        >
            <flux:icon.bell :variant="$unreadCount > 0 ? 'solid' : 'outline'" class="size-5" />
        </flux:button>

        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white leading-none ring-2 ring-white dark:ring-zinc-900">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </div>

    {{-- Dropdown panel --}}
    <div x-show="open"
         x-on:click.outside="open = false"
         x-transition
         class="absolute right-0 top-full mt-2 w-80 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 shadow-lg z-50 overflow-hidden"
         wire:ignore.self>

        <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-100 dark:border-zinc-800">
            <flux:heading size="sm">Notifications</flux:heading>
            @if($unreadCount > 0)
                <flux:button variant="ghost" size="xs" wire:click="markAllAsRead">
                    Mark all read
                </flux:button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($recentNotifications as $notif)
                <div wire:key="notif-{{ $notif['id'] }}"
                     class="flex gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors {{ is_null($notif['read_at']) ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}">
                    <div class="flex-1 min-w-0">
                        <flux:text size="sm" variant="{{ is_null($notif['read_at']) ? 'strong' : 'default' }}" class="truncate">
                            {{ $notif['title'] }}
                        </flux:text>
                        <flux:text size="xs" variant="muted" class="mt-0.5">
                            {{ $notif['message'] }}
                        </flux:text>
                        <flux:text size="xs" variant="muted" class="mt-1">
                            {{ $notif['created_at'] }}
                        </flux:text>
                    </div>
                    @if(is_null($notif['read_at']))
                        <button wire:click="markAsRead('{{ $notif['id'] }}')"
                                class="shrink-0 text-zinc-400 hover:text-blue-500 transition-colors"
                                title="Mark as read">
                            <flux:icon.check-circle variant="micro" />
                        </button>
                    @endif
                </div>
            @empty
                <div class="py-10 text-center">
                    <flux:icon.bell-slash class="size-8 mx-auto opacity-30 mb-2" />
                    <flux:text size="sm" variant="muted">No notifications yet</flux:text>
                </div>
            @endforelse
        </div>

        <div class="px-4 py-2 border-t border-zinc-100 dark:border-zinc-800">
            <flux:link href="{{ route('notifications.index') }}" wire:navigate class="text-sm text-blue-600 dark:text-blue-400">
                View all notifications
            </flux:link>
        </div>
    </div>
</div>
