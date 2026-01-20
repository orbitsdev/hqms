<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Appointments extends Component
{
    use WithPagination;

    public $filter = 'upcoming'; // upcoming, past, all
    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function cancelAppointment(int $appointmentId): void
    {
        $appointment = Appointment::findOrFail($appointmentId);
        
        if ($appointment->user_id !== Auth::id()) {
            abort(403);
        }
        
        if (!in_array($appointment->status, ['pending', 'approved'])) {
            $this->dispatch('error', 'Cannot cancel this appointment.');
            return;
        }
        
        $appointment->update(['status' => 'cancelled']);
        
        $this->dispatch('cancelled', 'Appointment cancelled successfully.');
    }

    public function getAppointments(): LengthAwarePaginator
    {
        $user = Auth::user();
        
        $query = $user->appointments()
            ->with(['consultationType', 'queue'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('chief_complaints', 'like', '%' . $this->search . '%')
                      ->orWhereHas('consultationType', function ($subQuery) {
                          $subQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            });

        switch ($this->filter) {
            case 'upcoming':
                $query->where('appointment_date', '>=', now())
                      ->whereIn('status', ['pending', 'approved']);
                break;
            case 'past':
                $query->where(function ($q) {
                    $q->where('appointment_date', '<', now())
                      ->orWhereIn('status', ['completed', 'cancelled', 'no_show']);
                });
                break;
            // 'all' - no additional filtering
        }

        return $query->orderBy('appointment_date', 'desc')
                     ->paginate(10);
    }

    public function render(): View
    {
        return view('livewire.patient.appointments', [
            'appointments' => $this->getAppointments(),
        ])->layout('layouts.patient');
    }
}
