<?php

namespace App\Livewire\Patient;

use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class MedicalRecords extends Component
{
    use WithPagination;

    public string $search = '';

    public string $consultationTypeFilter = '';

    public string $yearFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedConsultationTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedYearFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function consultationTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return ConsultationType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function availableYears(): array
    {
        return MedicalRecord::query()
            ->where('user_id', Auth::id())
            ->whereIn('status', ['completed', 'for_billing'])
            ->selectRaw('DISTINCT YEAR(visit_date) as year')
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->toArray();
    }

    #[Computed]
    public function stats(): array
    {
        $userId = Auth::id();

        return [
            'total' => MedicalRecord::where('user_id', $userId)
                ->whereIn('status', ['completed', 'for_billing'])
                ->count(),
            'this_year' => MedicalRecord::where('user_id', $userId)
                ->whereIn('status', ['completed', 'for_billing'])
                ->whereYear('visit_date', now()->year)
                ->count(),
        ];
    }

    public function render(): View
    {
        $records = MedicalRecord::query()
            ->where('user_id', Auth::id())
            ->whereIn('status', ['completed', 'for_billing'])
            ->with(['consultationType', 'doctor.personalInformation'])
            ->when($this->search, function ($query) {
                $search = '%'.$this->search.'%';
                $query->where(function ($q) use ($search) {
                    $q->where('diagnosis', 'like', $search)
                        ->orWhere('chief_complaints_initial', 'like', $search)
                        ->orWhere('chief_complaints_updated', 'like', $search)
                        ->orWhere('record_number', 'like', $search);
                });
            })
            ->when($this->consultationTypeFilter, fn ($q) => $q->where('consultation_type_id', $this->consultationTypeFilter))
            ->when($this->yearFilter, fn ($q) => $q->whereYear('visit_date', $this->yearFilter))
            ->orderByDesc('visit_date')
            ->paginate(10);

        return view('livewire.patient.medical-records', [
            'records' => $records,
        ])->layout('layouts.app');
    }
}
