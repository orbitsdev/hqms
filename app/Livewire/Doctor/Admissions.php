<?php

namespace App\Livewire\Doctor;

use App\Models\Admission;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class Admissions extends Component
{
    public string $status = 'active';

    public string $search = '';

    public ?int $selectedAdmissionId = null;

    // Edit modal
    public bool $showEditModal = false;

    #[Validate('nullable|string|max:50')]
    public string $roomNumber = '';

    #[Validate('nullable|string|max:50')]
    public string $bedNumber = '';

    #[Validate('nullable|string|max:1000')]
    public string $notes = '';

    // Discharge modal
    public bool $showDischargeModal = false;

    #[Validate('required|string|max:2000')]
    public string $dischargeSummary = '';

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->selectedAdmissionId = null;
    }

    public function selectAdmission(int $id): void
    {
        $this->selectedAdmissionId = $this->selectedAdmissionId === $id ? null : $id;
    }

    public function openEditModal(): void
    {
        if (! $this->selectedAdmission) {
            return;
        }

        $this->roomNumber = $this->selectedAdmission->room_number ?? '';
        $this->bedNumber = $this->selectedAdmission->bed_number ?? '';
        $this->notes = $this->selectedAdmission->notes ?? '';
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetValidation();
    }

    public function saveAdmission(): void
    {
        if (! $this->selectedAdmission) {
            return;
        }

        $this->validate([
            'roomNumber' => 'nullable|string|max:50',
            'bedNumber' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->selectedAdmission->update([
            'room_number' => $this->roomNumber ?: null,
            'bed_number' => $this->bedNumber ?: null,
            'notes' => $this->notes ?: null,
        ]);

        $this->closeEditModal();
        Toaster::success(__('Admission updated.'));
    }

    public function openDischargeModal(): void
    {
        if (! $this->selectedAdmission || $this->selectedAdmission->status !== 'active') {
            return;
        }

        $this->dischargeSummary = '';
        $this->showDischargeModal = true;
    }

    public function closeDischargeModal(): void
    {
        $this->showDischargeModal = false;
        $this->dischargeSummary = '';
        $this->resetValidation();
    }

    public function dischargePatient(): void
    {
        if (! $this->selectedAdmission || $this->selectedAdmission->status !== 'active') {
            Toaster::error(__('Invalid admission or already discharged.'));

            return;
        }

        $this->validate([
            'dischargeSummary' => 'required|string|max:2000',
        ]);

        $this->selectedAdmission->update([
            'status' => 'discharged',
            'discharge_date' => now(),
            'discharge_summary' => $this->dischargeSummary,
        ]);

        // Update medical record status to completed
        if ($this->selectedAdmission->medicalRecord) {
            $this->selectedAdmission->medicalRecord->update([
                'status' => 'completed',
            ]);
        }

        $this->closeDischargeModal();
        $this->selectedAdmissionId = null;
        Toaster::success(__('Patient discharged successfully.'));
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
        $doctor = Auth::user();

        $active = Admission::query()
            ->where('admitted_by', $doctor->id)
            ->where('status', 'active')
            ->count();

        $discharged = Admission::query()
            ->where('admitted_by', $doctor->id)
            ->where('status', 'discharged')
            ->count();

        return [
            'active' => $active,
            'discharged' => $discharged,
        ];
    }

    public function render(): View
    {
        $doctor = Auth::user();

        $admissions = Admission::query()
            ->with(['user.personalInformation', 'medicalRecord.consultationType', 'admittedBy'])
            ->where('admitted_by', $doctor->id)
            ->where('status', $this->status)
            ->when($this->search, fn ($q) => $q->whereHas('medicalRecord', fn ($mr) => $mr
                ->where('patient_first_name', 'like', "%{$this->search}%")
                ->orWhere('patient_last_name', 'like', "%{$this->search}%")
                ->orWhere('record_number', 'like', "%{$this->search}%"))
                ->orWhere('admission_number', 'like', "%{$this->search}%"))
            ->orderByDesc('admission_date')
            ->get();

        return view('livewire.doctor.admissions', [
            'admissions' => $admissions,
        ])->layout('layouts.app');
    }
}
