<?php
namespace App\Traits\Models;

use App\Models\ConsultationType;
use App\Models\Queue;
use Illuminate\Support\Collection;

trait QueueDisplayRelations
{
    public function consultationType()
    {
        return $this->belongsTo(ConsultationType::class);
    }

    protected static function bootQueueDisplayRelations(): void
    {
        static::creating(function ($display) {
            $display->access_token = bin2hex(random_bytes(32));
        });
    }

    public function isOnline(): bool
    {
        if (!$this->last_heartbeat) return false;

        return $this->last_heartbeat->gt(now()->subMinutes(5));
    }

    public function heartbeat(): void
    {
        $this->last_heartbeat = now();
        $this->save();
    }

    public function getCurrentQueue(): ?Queue
    {
        return Queue::where('consultation_type_id', $this->consultation_type_id)
            ->where('queue_date', today())
            ->where('status', 'serving')
            ->first();
    }

    public function getUpcomingQueues(int $limit = 5): Collection
    {
        return Queue::where('consultation_type_id', $this->consultation_type_id)
            ->where('queue_date', today())
            ->where('status', 'waiting')
            ->orderBy('queue_number')
            ->limit($limit)
            ->get();
    }
}
