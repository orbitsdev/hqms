<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

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

    public function cancelAppointment(): void
    {
        if (! in_array($this->appointment->status, ['confirmed', 'approved'])) {
            Toaster::error(__('Only confirmed or upcoming appointments can be cancelled.'));

            return;
        }

        $this->appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => __('Cancelled by patient'),
        ]);

        Toaster::success(__('Appointment cancelled successfully.'));

        $this->redirectRoute('patient.appointments', navigate: true);
    }

    public function render(): View
    {
        $possibleDoctors = collect();

        if (! $this->appointment->doctor_id && $this->appointment->consultationType) {
            $possibleDoctors = $this->appointment->consultationType->doctors()->get(['users.id', 'users.first_name', 'users.last_name']);
        }

        return view('livewire.patient.appointment-show', [
            'possibleDoctors' => $possibleDoctors,
        ])
            ->layout('layouts.app');
    }
}
