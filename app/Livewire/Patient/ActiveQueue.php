<?php

namespace App\Livewire\Patient;

use App\Models\Queue;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ActiveQueue extends Component
{
    public $activeQueue;
    public $currentServing;
    public $patientsAhead;
    public $estimatedTime;
    public $consultationType;
    public $statusColor = 'gray';

    protected $listeners = ['queueUpdated' => '$refresh'];

    public function mount(): void
    {
        $this->loadActiveQueue();
    }

    public function loadActiveQueue(): void
    {
        $user = Auth::user();
        
        $this->activeQueue = $user->queues()
            ->with('consultationType')
            ->where('status', 'waiting')
            ->first();
            
        if ($this->activeQueue) {
            $this->consultationType = $this->activeQueue->consultationType;
            $this->updateQueueStatus();
        }
    }

    public function updateQueueStatus(): void
    {
        if (!$this->activeQueue) {
            return;
        }

        // Get current serving number for this consultation type
        $this->currentServing = Queue::where('consultation_type_id', $this->activeQueue->consultation_type_id)
            ->where('status', 'serving')
            ->orderBy('queue_number')
            ->first();
            
        // Count patients ahead
        $this->patientsAhead = Queue::where('consultation_type_id', $this->activeQueue->consultation_type_id)
            ->where('status', 'waiting')
            ->where('queue_number', '<', $this->activeQueue->queue_number)
            ->count();
            
        // Calculate estimated time (simplified - 15 minutes per patient)
        $avgServiceTime = 15; // minutes
        $this->estimatedTime = now()->addMinutes(($this->patientsAhead + 1) * $avgServiceTime);
        $this->statusColor = $this->getStatusColor();
    }

    public function getQueuePosition(): ?int
    {
        if (!$this->activeQueue) {
            return null;
        }
        
        return Queue::where('consultation_type_id', $this->activeQueue->consultation_type_id)
            ->where('status', 'waiting')
            ->where('queue_number', '<=', $this->activeQueue->queue_number)
            ->count();
    }

    public function getStatusColor(): string
    {
        if (!$this->activeQueue) {
            return 'gray';
        }
        
        if ($this->patientsAhead <= 2) {
            return 'red'; // Urgent - your turn soon
        } elseif ($this->patientsAhead <= 5) {
            return 'yellow'; // Getting close
        } else {
            return 'blue'; // Still waiting
        }
    }

    public function render(): View
    {
        return view('livewire.patient.active-queue')
            ->layout('layouts.app');
    }
}
