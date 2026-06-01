<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class UserLoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $ipAddress,
        public readonly string $userAgent,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Login Detected',
            'message' => "Login from {$this->ipAddress}",
            'icon' => 'arrow-right-end-on-rectangle',
            'color' => 'blue',
            'url' => '/activity-logs',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Login Detected',
            'message' => "Login from {$this->ipAddress}",
            'icon' => 'arrow-right-end-on-rectangle',
            'color' => 'blue',
            'url' => '/activity-logs',
        ]);
    }

    public function broadcastType(): string
    {
        return 'NotificationSent';
    }
}
