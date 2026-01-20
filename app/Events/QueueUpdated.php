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
     * The action that triggered the event.
     */
    public string $action;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Queue $queue,
        string $action = 'updated'
    ) {
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
            // Public channel for queue displays in waiting area
            new Channel('queue.display.'.$this->queue->consultation_type_id),
            // Private channel for staff dashboard
            new PrivateChannel('queue.staff'),
        ];

        // If patient has a user account, notify them directly
        if ($this->queue->user_id) {
            $channels[] = new PrivateChannel('queue.patient.'.$this->queue->user_id);
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
                'id' => $this->queue->id,
                'queue_number' => $this->queue->queue_number,
                'status' => $this->queue->status,
                'priority' => $this->queue->priority,
                'consultation_type_id' => $this->queue->consultation_type_id,
                'doctor_id' => $this->queue->doctor_id,
                'called_at' => $this->queue->called_at?->toIso8601String(),
                'serving_started_at' => $this->queue->serving_started_at?->toIso8601String(),
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
