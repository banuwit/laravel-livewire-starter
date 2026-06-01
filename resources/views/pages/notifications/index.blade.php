<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Notifications')] class extends Component {
    use WithPagination;

    public string $filter = 'all'; // 'all' | 'unread' | 'read'

    public function markAsRead(string $id): void
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first()
            ?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function deleteNotification(string $id): void
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first()
            ?->delete();
    }

    public function render()
    {
        $query = auth()->user()->notifications();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return view('pages.notifications.index', [
            'notifications' => $query->latest()->paginate(20),
            'unreadCount' => auth()->user()->unreadNotifications()->count(),
        ]);
    }
};
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading>Notifications</flux:heading>
            <flux:subheading>Manage your notifications</flux:subheading>
        </div>
        @if($unreadCount > 0)
            <flux:button variant="primary" size="sm" wire:click="markAllAsRead">
                Mark all as read
            </flux:button>
        @endif
    </div>

    {{-- Filter tabs --}}
    <div class="flex gap-2">
        <flux:button
            variant="{{ $filter === 'all' ? 'primary' : 'ghost' }}"
            size="sm"
            wire:click="$set('filter', 'all')"
        >
            All
        </flux:button>
        <flux:button
            variant="{{ $filter === 'unread' ? 'primary' : 'ghost' }}"
            size="sm"
            wire:click="$set('filter', 'unread')"
        >
            Unread
            @if($unreadCount > 0)
                <flux:badge variant="warning">{{ $unreadCount }}</flux:badge>
            @endif
        </flux:button>
        <flux:button
            variant="{{ $filter === 'read' ? 'primary' : 'ghost' }}"
            size="sm"
            wire:click="$set('filter', 'read')"
        >
            Read
        </flux:button>
    </div>

    {{-- Notifications list --}}
    <div class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        @forelse($notifications as $notification)
            <div
                wire:key="notif-{{ $notification->id }}"
                class="flex items-start gap-4 px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 last:border-b-0 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors {{ is_null($notification->read_at) ? 'bg-blue-50/30 dark:bg-blue-900/10' : '' }}"
            >
                <div class="flex-1">
                    <flux:text size="sm" variant="{{ is_null($notification->read_at) ? 'strong' : 'default' }}">
                        {{ $notification->data['title'] ?? 'Notification' }}
                    </flux:text>
                    <flux:text size="sm" variant="muted" class="mt-1">
                        {{ $notification->data['message'] ?? '' }}
                    </flux:text>
                    <flux:text size="xs" variant="muted" class="mt-2">
                        {{ $notification->created_at->diffForHumans() }}
                    </flux:text>
                </div>

                <div class="flex items-center gap-2">
                    @if(is_null($notification->read_at))
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="check"
                            wire:click="markAsRead('{{ $notification->id }}')"
                            title="Mark as read"
                        />
                    @endif

                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="trash"
                        wire:click="deleteNotification('{{ $notification->id }}')"
                        title="Delete"
                    />
                </div>
            </div>
        @empty
            <div class="py-12 text-center">
                <flux:icon.bell-slash class="size-12 mx-auto opacity-30 mb-4" />
                <flux:heading size="md">No notifications</flux:heading>
                <flux:subheading>You're all caught up!</flux:subheading>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</div>
