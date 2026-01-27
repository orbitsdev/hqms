<?php

namespace App\Livewire\Doctor;

use App\Models\MedicalRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class PatientHistory extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $selectedRecordId = null;

    public bool $showDetailModal = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function viewRecord(int $id): void
    {
        $this->selectedRecordId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedRecordId = null;
    }

    #[Computed]
    public function selectedRecord(): ?MedicalRecord
    {
        if (! $this->selectedRecordId) {
            return null;
        }

        return MedicalRecord::with([
            'prescriptions.hospitalDrug',
            'consultationType',
            'doctor',
            'nurse',
        ])->find($this->selectedRecordId);
    }

    public function render(): View
    {
        $doctor = Auth::user();
        $search = trim($this->search);

        // Get unique patients from medical records examined by this doctor
        // or from doctor's consultation types
        $consultationTypeIds = $doctor->consultationTypes()->pluck('consultation_types.id');

        $records = MedicalRecord::query()
            ->with(['consultationType', 'doctor'])
            ->where(function ($q) use ($doctor, $consultationTypeIds) {
                $q->where('doctor_id', $doctor->id)
                    ->orWhereIn('consultation_type_id', $consultationTypeIds);
            })
            ->whereIn('status', ['for_billing', 'for_admission', 'completed'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('patient_first_name', 'like', "%{$search}%")
                        ->orWhere('patient_last_name', 'like', "%{$search}%")
                        ->orWhere('record_number', 'like', "%{$search}%")
                        ->orWhere('diagnosis', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('visit_date')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.doctor.patient-history', [
            'records' => $records,
        ])->layout('layouts.app');
    }
}
