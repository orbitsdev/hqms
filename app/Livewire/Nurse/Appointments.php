<?php

namespace App\Livewire\Nurse;

use App\Models\Appointment;
use App\Models\ConsultationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Appointments extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'pending';

    public string $consultationTypeFilter = '';

    public string $dateFilter = '';

    public string $sortBy = 'appointment_date';

    public string $sortDirection = 'asc';

    public string $sourceFilter = '';

    /** @var array<string, mixed> */
    protected array $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'pending'],
        'consultationTypeFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
        'sourceFilter' => ['except' => ''],
        'sortBy' => ['except' => 'appointment_date'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedConsultationTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSourceFilter(): void
    {
        $this->resetPage();
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'consultationTypeFilter', 'dateFilter', 'sourceFilter']);
        $this->resetPage();
    }

    /** @return array<string, int> */
    public function getStatusCountsProperty(): array
    {
        $baseQuery = Appointment::query();

        return [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'today' => (clone $baseQuery)->whereDate('appointment_date', today())->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];
    }

    public function render(): View
    {
        $search = trim($this->search);

        $appointments = Appointment::query()
            ->with([
                'user.personalInformation',
                'consultationType',
                'doctor',
                'queue.consultationType',
                'approvedBy',
            ])
            ->when($this->status === 'pending', fn (Builder $q) => $q->where('status', 'pending'))
            ->when($this->status === 'approved', fn (Builder $q) => $q->where('status', 'approved'))
            ->when($this->status === 'cancelled', fn (Builder $q) => $q->where('status', 'cancelled'))
            ->when($this->status === 'today', fn (Builder $q) => $q->whereDate('appointment_date', today()))
            ->when($this->consultationTypeFilter !== '', fn (Builder $q) => $q->where('consultation_type_id', $this->consultationTypeFilter))
            ->when($this->dateFilter !== '', fn (Builder $q) => $q->whereDate('appointment_date', $this->dateFilter))
            ->when($this->sourceFilter !== '', fn (Builder $q) => $q->where('source', $this->sourceFilter))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $likeSearch = '%'.$search.'%';

                $query->where(function (Builder $q) use ($likeSearch): void {
                    $q->where('patient_first_name', 'like', $likeSearch)
                        ->orWhere('patient_middle_name', 'like', $likeSearch)
                        ->orWhere('patient_last_name', 'like', $likeSearch)
                        ->orWhere('patient_phone', 'like', $likeSearch)
                        ->orWhere('chief_complaints', 'like', $likeSearch)
                        ->orWhereHas('consultationType', fn (Builder $ct) => $ct->where('name', 'like', $likeSearch))
                        ->orWhereHas('user', fn (Builder $u) => $u->where('email', 'like', $likeSearch));
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        $consultationTypes = ConsultationType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.nurse.appointments', [
            'appointments' => $appointments,
            'consultationTypes' => $consultationTypes,
            'statusCounts' => $this->statusCounts,
        ])->layout('layouts.app');
    }
}
