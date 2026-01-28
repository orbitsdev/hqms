<?php

namespace App\Livewire\Nurse;

use App\Models\Admission;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Admissions extends Component
{
    public string $status = 'active';

    public string $search = '';

    public ?int $selectedAdmissionId = null;

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->selectedAdmissionId = null;
    }

    public function selectAdmission(int $id): void
    {
        $this->selectedAdmissionId = $this->selectedAdmissionId === $id ? null : $id;
    }

    #[Computed]
    public function selectedAdmission(): ?Admission
    {
        if (! $this->selectedAdmissionId) {
            return null;
        }

        return Admission::with([
            'user.personalInformation',
            'medicalRecord.consultationType',
            'medicalRecord.prescriptions',
            'admittedBy',
        ])->find($this->selectedAdmissionId);
    }

    #[Computed]
    public function statusCounts(): array
    {
        $active = Admission::where('status', 'active')->count();
        $discharged = Admission::where('status', 'discharged')->count();

        return [
            'active' => $active,
            'discharged' => $discharged,
        ];
    }

    public function render(): View
    {
        $admissions = Admission::query()
            ->with(['user.personalInformation', 'medicalRecord.consultationType', 'admittedBy'])
            ->where('status', $this->status)
            ->when($this->search, fn ($q) => $q->whereHas('medicalRecord', fn ($mr) => $mr
                ->where('patient_first_name', 'like', "%{$this->search}%")
                ->orWhere('patient_last_name', 'like', "%{$this->search}%")
                ->orWhere('record_number', 'like', "%{$this->search}%"))
                ->orWhere('admission_number', 'like', "%{$this->search}%"))
            ->orderByDesc('admission_date')
            ->get();

        return view('livewire.nurse.admissions', [
            'admissions' => $admissions,
        ])->layout('layouts.app');
    }
}
