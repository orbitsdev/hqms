<?php

namespace App\Livewire\Cashier;

use App\Models\MedicalRecord;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class BillingQueue extends Component
{
    use WithPagination;

    public string $search = '';

    public string $consultationFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedConsultationFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function consultationTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\ConsultationType::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function queueCount(): int
    {
        return MedicalRecord::where('status', 'for_billing')->count();
    }

    public function render(): View
    {
        $records = MedicalRecord::query()
            ->where('status', 'for_billing')
            ->with(['consultationType', 'doctor', 'prescriptions' => function ($q) {
                $q->where('is_hospital_drug', true);
            }])
            ->when($this->consultationFilter, fn ($q) => $q->where('consultation_type_id', $this->consultationFilter))
            ->when($this->search, fn ($q) => $q
                ->where(function ($query) {
                    $query->where('patient_first_name', 'like', "%{$this->search}%")
                        ->orWhere('patient_last_name', 'like', "%{$this->search}%")
                        ->orWhere('record_number', 'like', "%{$this->search}%");
                }))
            ->orderBy('examination_ended_at')
            ->paginate(15);

        return view('livewire.cashier.billing-queue', [
            'records' => $records,
        ])->layout('layouts.app');
    }
}
