<?php

namespace App\Livewire\Nurse;

use App\Models\Appointment;
use App\Models\Queue;
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    /** @return array<string, int> */
    public function getStatsProperty(): array
    {
        return [
            'pending_appointments' => Appointment::query()
                ->where('status', 'pending')
                ->count(),
            'today_appointments' => Appointment::query()
                ->whereDate('appointment_date', today())
                ->count(),
            'waiting_checkin' => Appointment::query()
                ->where('status', 'approved')
                ->whereDate('appointment_date', today())
                ->count(),
            'queue_waiting' => Queue::query()
                ->today()
                ->where('status', 'waiting')
                ->count(),
            'queue_serving' => Queue::query()
                ->today()
                ->where('status', 'serving')
                ->count(),
            'queue_completed' => Queue::query()
                ->today()
                ->where('status', 'completed')
                ->count(),
        ];
    }

    public function render(): View
    {
        $currentServing = Queue::query()
            ->with(['consultationType', 'appointment'])
            ->today()
            ->where('status', 'serving')
            ->orderBy('queue_number')
            ->get();

        $recentQueue = Queue::query()
            ->with(['consultationType', 'appointment'])
            ->today()
            ->whereIn('status', ['waiting', 'called'])
            ->orderByRaw("FIELD(priority, 'emergency', 'urgent', 'normal')")
            ->orderBy('queue_number')
            ->limit(5)
            ->get();

        return view('livewire.nurse.dashboard', [
            'stats' => $this->stats,
            'currentServing' => $currentServing,
            'recentQueue' => $recentQueue,
        ])->layout('layouts.app');
    }
}
