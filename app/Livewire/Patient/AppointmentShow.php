<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class AppointmentShow extends Component
{
    public $appointment;
    public $canCancel = false;
    public $statusColor = 'gray';

    public function mount(Appointment $appointment): void
    {
        $this->appointment = $appointment->load(['consultationType', 'queue', 'user.personalInformation']);

        if ($this->appointment->user_id !== Auth::id()) {
            abort(403);
        }

        $this->canCancel = in_array($this->appointment->status, ['pending', 'approved']);
        $this->statusColor = $this->getStatusColor();
    }

    public function cancelAppointment(): void
    {
        if (!$this->canCancel) {
            $this->dispatch('error', 'Cannot cancel this appointment.');
            return;
        }
        
        $this->appointment->update(['status' => 'cancelled']);

        $this->canCancel = false;
        $this->statusColor = $this->getStatusColor();
        $this->dispatch('cancelled', 'Appointment cancelled successfully.');
    }

    public function getStatusColor(): string
    {
        return match($this->appointment->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'completed' => 'blue',
            'cancelled' => 'red',
            'no_show' => 'red',
            default => 'gray'
        };
    }

    public function render(): View
    {
        return view('livewire.patient.appointment-show')
            ->layout('layouts.app');
    }
}
