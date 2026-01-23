<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class AppointmentShow extends Component
{
    public Appointment $appointment;

    public function mount(Appointment $appointment): void
    {
        $userId = Auth::id();

        if (! $userId || $appointment->user_id !== $userId) {
            abort(404);
        }

        $this->appointment = $appointment->loadMissing([
            'consultationType',
            'doctor',
            'queue.consultationType',
        ]);
    }

    public function render(): View
    {
        return view('livewire.patient.appointment-show')
            ->layout('layouts.app');
    }
}
