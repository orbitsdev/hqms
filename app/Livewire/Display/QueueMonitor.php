<?php

namespace App\Livewire\Display;

use App\Models\ConsultationType;
use App\Models\Queue;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class QueueMonitor extends Component
{
    public ?int $consultationTypeId = null;

    public ?ConsultationType $consultationType = null;

    public function mount(?int $type = null): void
    {
        $this->consultationTypeId = $type;

        if ($type) {
            $this->consultationType = ConsultationType::find($type);
        }
    }

    /**
     * Refresh when queue updates come in via Echo.
     */
    #[On('echo:queue.display.{consultationTypeId},queue.updated')]
    public function refreshOnQueueUpdate(): void
    {
        // Component will automatically re-render
    }

    /**
     * Get the currently called queue (most recent).
     */
    public function getCalledQueueProperty(): ?Queue
    {
        return Queue::query()
            ->today()
            ->when($this->consultationTypeId, fn ($q) => $q->where('consultation_type_id', $this->consultationTypeId))
            ->where('status', 'called')
            ->orderByDesc('called_at')
            ->first();
    }

    /**
     * Get currently serving queues.
     */
    public function getServingQueuesProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return Queue::query()
            ->today()
            ->when($this->consultationTypeId, fn ($q) => $q->where('consultation_type_id', $this->consultationTypeId))
            ->where('status', 'serving')
            ->orderBy('serving_started_at')
            ->limit(3)
            ->get();
    }

    /**
     * Get next waiting queues.
     */
    public function getNextQueuesProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return Queue::query()
            ->today()
            ->when($this->consultationTypeId, fn ($q) => $q->where('consultation_type_id', $this->consultationTypeId))
            ->where('status', 'waiting')
            ->orderByRaw("CASE priority WHEN 'emergency' THEN 1 WHEN 'urgent' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")
            ->orderBy('queue_number')
            ->limit(5)
            ->get();
    }

    /**
     * Get count of waiting patients.
     */
    public function getWaitingCountProperty(): int
    {
        return Queue::query()
            ->today()
            ->when($this->consultationTypeId, fn ($q) => $q->where('consultation_type_id', $this->consultationTypeId))
            ->where('status', 'waiting')
            ->count();
    }

    public function render(): View
    {
        return view('livewire.display.queue-monitor', [
            'calledQueue' => $this->calledQueue,
            'servingQueues' => $this->servingQueues,
            'nextQueues' => $this->nextQueues,
            'waitingCount' => $this->waitingCount,
        ])->layout('layouts.display');
    }
}
