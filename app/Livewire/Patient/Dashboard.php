<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Queue;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function user()
    {
        return Auth::user();
    }

    #[Computed]
    public function activeQueue()
    {
        return Queue::query()
            ->whereHas('appointment', fn ($q) => $q->where('user_id', $this->user->id))
            ->whereDate('queue_date', today())
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->with(['consultationType', 'appointment'])
            ->first();
    }

    #[Computed]
    public function upcomingAppointments()
    {
        return Appointment::query()
            ->where('user_id', $this->user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('appointment_date', '>=', today())
            ->with(['consultationType', 'doctor'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(3)
            ->get();
    }

    #[Computed]
    public function recentRecords()
    {
        return MedicalRecord::query()
            ->where('user_id', $this->user->id)
            ->whereIn('status', ['completed', 'for_billing'])
            ->with(['consultationType', 'doctor'])
            ->orderByDesc('visit_date')
            ->limit(3)
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total_visits' => MedicalRecord::where('user_id', $this->user->id)
                ->whereIn('status', ['completed', 'for_billing'])
                ->count(),
            'pending_appointments' => Appointment::where('user_id', $this->user->id)
                ->where('status', 'pending')
                ->count(),
            'upcoming_appointments' => Appointment::where('user_id', $this->user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where('appointment_date', '>=', today())
                ->count(),
        ];
    }

    public function render(): View
    {
        return view('livewire.patient.dashboard')
            ->layout('layouts.app');
    }
}
