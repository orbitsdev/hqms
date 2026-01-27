<?php

namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The queue entry that was updated.
     */
    public Queue $queueEntry;

    /**
     * The action that triggered the event.
     */
    public string $action;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Queue $queueEntry,
        string $action = 'updated'
    ) {
        $this->queueEntry = $queueEntry;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            // Public channel for queue displays in waiting area (specific type)
            new Channel('queue.display.'.$this->queueEntry->consultation_type_id),
            // Public channel for all queue displays (shows all services)
            new Channel('queue.display.all'),
            // Private channel for staff dashboard
            new PrivateChannel('queue.staff'),
        ];

        // If patient has a user account, notify them directly
        if ($this->queueEntry->user_id) {
            $channels[] = new PrivateChannel('queue.patient.'.$this->queueEntry->user_id);
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'queue' => [
                'id' => $this->queueEntry->id,
                'queue_number' => $this->queueEntry->queue_number,
                'status' => $this->queueEntry->status,
                'priority' => $this->queueEntry->priority,
                'consultation_type_id' => $this->queueEntry->consultation_type_id,
                'doctor_id' => $this->queueEntry->doctor_id,
                'called_at' => $this->queueEntry->called_at?->toIso8601String(),
                'serving_started_at' => $this->queueEntry->serving_started_at?->toIso8601String(),
            ],
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'queue.updated';
    }
}
