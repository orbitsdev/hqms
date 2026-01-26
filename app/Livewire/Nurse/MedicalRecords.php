<?php

namespace App\Livewire\Nurse;

use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class MedicalRecords extends Component
{
    use WithPagination;

    // ==================== SEARCH & FILTERS ====================
    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $consultationTypeFilter = '';

    #[Url(history: true)]
    public string $doctorFilter = '';

    #[Url(history: true)]
    public string $statusFilter = '';

    #[Url(history: true)]
    public string $visitTypeFilter = '';

    #[Url(history: true)]
    public string $dateFrom = '';

    #[Url(history: true)]
    public string $dateTo = '';

    #[Url(history: true)]
    public string $sortField = 'visit_date';

    #[Url(history: true)]
    public string $sortDirection = 'desc';

    public bool $showFilters = false;

    // ==================== LIFECYCLE ====================

    public function mount(): void
    {
        // Default to last 30 days if no date filter
        if (! $this->dateFrom && ! $this->dateTo) {
            $this->dateFrom = now()->subDays(30)->format('Y-m-d');
            $this->dateTo = now()->format('Y-m-d');
        }
    }

    // ==================== FILTER METHODS ====================

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedConsultationTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDoctorFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedVisitTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->consultationTypeFilter = '';
        $this->doctorFilter = '';
        $this->statusFilter = '';
        $this->visitTypeFilter = '';
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    // ==================== SORTING ====================

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    // ==================== COMPUTED PROPERTIES ====================

    /** @return LengthAwarePaginator<MedicalRecord> */
    #[Computed]
    public function records(): LengthAwarePaginator
    {
        $query = MedicalRecord::query()
            ->with(['consultationType', 'doctor', 'nurse', 'appointment', 'queue']);

        // Search by patient name or record number
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('record_number', 'like', "%{$search}%")
                    ->orWhere('patient_first_name', 'like', "%{$search}%")
                    ->orWhere('patient_last_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(patient_first_name, ' ', patient_last_name) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("CONCAT(patient_last_name, ', ', patient_first_name) LIKE ?", ["%{$search}%"]);
            });
        }

        // Filter by consultation type
        if ($this->consultationTypeFilter) {
            $query->where('consultation_type_id', $this->consultationTypeFilter);
        }

        // Filter by doctor
        if ($this->doctorFilter) {
            $query->where('doctor_id', $this->doctorFilter);
        }

        // Filter by status
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Filter by visit type
        if ($this->visitTypeFilter) {
            $query->where('visit_type', $this->visitTypeFilter);
        }

        // Filter by date range
        if ($this->dateFrom) {
            $query->whereDate('visit_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('visit_date', '<=', $this->dateTo);
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate(15);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, ConsultationType> */
    #[Computed]
    public function consultationTypes()
    {
        return ConsultationType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, User> */
    #[Computed]
    public function doctors()
    {
        return User::role('doctor')
            ->orderBy('first_name')
            ->get();
    }

    /** @return array<string, int> */
    #[Computed]
    public function stats(): array
    {
        $today = now()->toDateString();
        $thisMonth = now()->startOfMonth()->toDateString();

        return [
            'today' => MedicalRecord::whereDate('visit_date', $today)->count(),
            'this_month' => MedicalRecord::whereDate('visit_date', '>=', $thisMonth)->count(),
            'in_progress' => MedicalRecord::where('status', 'in_progress')->count(),
            'for_billing' => MedicalRecord::where('status', 'for_billing')->count(),
        ];
    }

    /** @return array<string, string> */
    public function getStatusOptionsProperty(): array
    {
        return [
            'in_progress' => __('In Progress'),
            'for_billing' => __('For Billing'),
            'for_admission' => __('For Admission'),
            'completed' => __('Completed'),
        ];
    }

    /** @return array<string, string> */
    public function getVisitTypeOptionsProperty(): array
    {
        return [
            'new' => __('New Patient'),
            'old' => __('Old Patient'),
            'revisit' => __('Revisit'),
        ];
    }

    public function render(): View
    {
        return view('livewire.nurse.medical-records', [
            'records' => $this->records,
            'consultationTypes' => $this->consultationTypes,
            'doctors' => $this->doctors,
            'stats' => $this->stats,
            'statusOptions' => $this->statusOptions,
            'visitTypeOptions' => $this->visitTypeOptions,
        ])->layout('layouts.app');
    }
}
