<?php

namespace App\Livewire\Patient;

use App\Models\Queue;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ActiveQueue extends Component
{
    /**
     * Refresh when queue updates come in via Echo.
     */
    #[On('echo-private:queue.patient.{userId},queue.updated')]
    public function refreshOnQueueUpdate(): void
    {
        // Component will automatically re-render
    }

    #[Computed]
    public function userId(): int
    {
        return Auth::id();
    }

    #[Computed]
    public function activeQueue(): ?Queue
    {
        return Queue::query()
            ->whereHas('appointment', fn ($q) => $q->where('user_id', Auth::id()))
            ->whereDate('queue_date', today())
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->with(['consultationType', 'appointment', 'medicalRecord', 'servedBy.personalInformation'])
            ->first();
    }

    #[Computed]
    public function queuePosition(): ?int
    {
        $queue = $this->activeQueue;

        if (! $queue || $queue->status !== 'waiting') {
            return null;
        }

        return Queue::query()
            ->where('consultation_type_id', $queue->consultation_type_id)
            ->whereDate('queue_date', today())
            ->where('status', 'waiting')
            ->where(function ($q) use ($queue) {
                $q->where('queue_number', '<', $queue->queue_number)
                    ->orWhere(function ($subQ) use ($queue) {
                        $subQ->where('queue_number', '=', $queue->queue_number)
                            ->where('id', '<', $queue->id);
                    });
            })
            ->count() + 1;
    }

    #[Computed]
    public function estimatedWaitMinutes(): ?int
    {
        $position = $this->queuePosition;

        if ($position === null || $position <= 1) {
            return null;
        }

        // Estimate based on average 10 minutes per patient
        return ($position - 1) * 10;
    }

    #[Computed]
    public function currentlyServing(): ?\Illuminate\Database\Eloquent\Collection
    {
        $queue = $this->activeQueue;

        if (! $queue) {
            return null;
        }

        return Queue::query()
            ->where('consultation_type_id', $queue->consultation_type_id)
            ->whereDate('queue_date', today())
            ->where('status', 'serving')
            ->with('consultationType')
            ->get();
    }

    #[Computed]
    public function recentHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return Queue::query()
            ->whereHas('appointment', fn ($q) => $q->where('user_id', Auth::id()))
            ->whereIn('status', ['completed', 'skipped', 'cancelled'])
            ->with(['consultationType', 'appointment'])
            ->orderByDesc('queue_date')
            ->limit(5)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.patient.active-queue', [
            'activeQueue' => $this->activeQueue,
            'queuePosition' => $this->queuePosition,
            'estimatedWaitMinutes' => $this->estimatedWaitMinutes,
            'currentlyServing' => $this->currentlyServing,
            'recentHistory' => $this->recentHistory,
        ])->layout('layouts.app');
    }
}
