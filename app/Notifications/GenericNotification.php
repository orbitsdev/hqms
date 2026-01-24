<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\Channel;

class GenericNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $payload)
    {
        $this->payload['time'] ??= now()->toISOString();
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        // always store receiver id for channel + UI filtering
        $this->payload['receiver_id'] ??= $notifiable->id;

        return $this->payload;
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $data = $this->toArray($notifiable);

        return new BroadcastMessage($data);
    }

    public function broadcastOn(): array
    {
        $receiverId = $this->payload['receiver_id'] ?? null;

        // fallback channel if missing (to avoid crash)
        if (! $receiverId) {
            return [new Channel('notifications.public')];
        }

        return [new Channel('notifications.' . $receiverId)];
    }
}
