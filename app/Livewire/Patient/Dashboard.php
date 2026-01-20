<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use App\Models\Queue;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public $upcomingAppointment;
    public $activeQueue;
    public $userName;

    public function mount(): void
    {
        $user = Auth::user();
        $this->userName = $user->personalInformation?->full_name ?? $user->email;
        
        $this->upcomingAppointment = $user->appointments()
            ->whereIn('status', ['approved', 'pending'])
            ->where('appointment_date', '>=', now())
            ->with(['consultationType', 'queue'])
            ->orderBy('appointment_date')
            ->first();
            
        $this->activeQueue = $user->queues()
            ->with('consultationType')
            ->where('status', 'waiting')
            ->first();
    }

    public function render(): View
    {
        return view('livewire.patient.dashboard')
            ->layout('layouts.patient');
    }
}
