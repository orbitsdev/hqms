<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Appointments extends Component
{
    use WithPagination;

    public string $filter = 'upcoming';

    public string $search = '';

    protected array $queryString = [
        'filter' => ['except' => 'upcoming'],
        'search' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $direction = $this->filter === 'past' ? 'desc' : 'asc';
        $search = trim($this->search);
        $userId = Auth::id() ?? 0;

        $appointments = Appointment::query()
            ->where('user_id', $userId)
            ->with(['consultationType', 'doctor', 'queue.consultationType'])
            ->when($this->filter === 'upcoming', function (Builder $query): void {
                $query->whereDate('appointment_date', '>=', today());
            })
            ->when($this->filter === 'past', function (Builder $query): void {
                $query->whereDate('appointment_date', '<', today());
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $likeSearch = '%' . $search . '%';

                $query->where(function (Builder $query) use ($likeSearch): void {
                    $query->whereHas('consultationType', function (Builder $query) use ($likeSearch): void {
                        $query->where('name', 'like', $likeSearch);
                    })
                        ->orWhere('patient_first_name', 'like', $likeSearch)
                        ->orWhere('patient_middle_name', 'like', $likeSearch)
                        ->orWhere('patient_last_name', 'like', $likeSearch)
                        ->orWhere('status', 'like', $likeSearch)
                        ->orWhere('chief_complaints', 'like', $likeSearch);
                });
            })
            ->orderBy('appointment_date', $direction)
            ->orderBy('appointment_time', $direction)
            ->paginate(8);

        return view('livewire.patient.appointments')
            ->with('appointments', $appointments)
            ->layout('layouts.app');
    }
}
