<?php

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;
    public array $recentNotifications = [];
    public bool $dropdownOpen = false;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    protected function loadNotifications(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $this->unreadCount = $user->unreadNotifications()->count();
        $this->recentNotifications = $user->notifications()
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Notification',
                'message' => $n->data['message'] ?? '',
                'icon' => $n->data['icon'] ?? 'bell',
                'color' => $n->data['color'] ?? 'zinc',
                'url' => $n->data['url'] ?? null,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    #[On('echo-private:App.Models.User.{userId},NotificationSent')]
    public function onNotificationReceived(array $event): void
    {
        $this->loadNotifications();

        $this->dispatch('show-notification-toast',
            title: $event['title'] ?? 'New notification',
            message: $event['message'] ?? '',
        );
    }

    public function markAsRead(string $id): void
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first()
            ?->markAsRead();

        $this->loadNotifications();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function getUserIdProperty(): int|string
    {
        return auth()->id();
    }

    public function render()
    {
        return view('components.notification.bell');
    }
}
