<?php

namespace App\Console\Commands;

use App\Events\QueueUpdated;
use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Broadcast;

class TestBroadcast extends Command
{
    protected $signature = 'app:test-broadcast {--sync : Broadcast synchronously without queue}';

    protected $description = 'Test broadcasting to queue display channels';

    public function handle(): int
    {
        $this->info('Testing Reverb broadcast...');

        // Find a queue entry to test with
        $queue = Queue::whereDate('created_at', today())->first();

        if ($queue) {
            $this->info("Found Queue #{$queue->queue_number} (ID: {$queue->id})");
            $this->info("Consultation Type ID: {$queue->consultation_type_id}");

            if ($this->option('sync')) {
                // Broadcast immediately without queue using ShouldBroadcastNow
                $this->info('Broadcasting synchronously (no queue worker needed)...');

                // Use Broadcast facade directly for sync
                $event = new QueueUpdated($queue, 'test');
                Broadcast::on($event->broadcastOn())
                    ->as($event->broadcastAs())
                    ->with($event->broadcastWith())
                    ->sendNow();
            } else {
                // Normal broadcast through queue
                $this->info('Broadcasting through queue worker...');
                event(new QueueUpdated($queue, 'test'));
            }

            $this->info('Event dispatched! Check:');
            $this->line('  - Reverb terminal for broadcast message');
            $this->line('  - Display page for update');

            return Command::SUCCESS;
        }

        $this->warn('No queue entries found for today.');
        $this->info('Broadcasting test message directly to queue.display.all...');

        // Direct broadcast without model
        Broadcast::on(new Channel('queue.display.all'))->as('queue.updated')->with([
            'action' => 'test',
            'queue' => [
                'id' => 0,
                'queue_number' => 999,
                'status' => 'called',
                'priority' => 'normal',
                'consultation_type_id' => 1,
            ],
        ])->sendNow();

        $this->info('Test broadcast sent!');

        return Command::SUCCESS;
    }
}
