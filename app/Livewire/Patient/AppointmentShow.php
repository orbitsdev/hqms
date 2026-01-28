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
        if ($this->appointment->status !== 'pending') {
            Toaster::error(__('Only pending appointments can be cancelled.'));

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
        return view('livewire.patient.appointment-show')
            ->layout('layouts.app');
    }
}
