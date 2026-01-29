<?php

namespace App\Livewire\Patient;

use App\Models\MedicalRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class MedicalRecordShow extends Component
{
    #[Locked]
    public int $medicalRecordId;

    public function mount(MedicalRecord $medicalRecord): void
    {
        // Ensure the record belongs to the current user
        if ($medicalRecord->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to view this record.');
        }

        $this->medicalRecordId = $medicalRecord->id;
    }

    #[Computed]
    public function record(): MedicalRecord
    {
        return MedicalRecord::with([
            'consultationType',
            'doctor.personalInformation',
            'nurse.personalInformation',
            'prescriptions.hospitalDrug',
            'billingTransaction.billingItems',
        ])->findOrFail($this->medicalRecordId);
    }

    #[Computed]
    public function vitalSigns(): array
    {
        $record = $this->record;

        $vitals = [];

        if ($record->temperature) {
            $vitals[] = ['label' => __('Temperature'), 'value' => $record->temperature.' C'];
        }
        if ($record->blood_pressure) {
            $vitals[] = ['label' => __('Blood Pressure'), 'value' => $record->blood_pressure.' mmHg'];
        }
        if ($record->cardiac_rate) {
            $vitals[] = ['label' => __('Heart Rate'), 'value' => $record->cardiac_rate.' bpm'];
        }
        if ($record->respiratory_rate) {
            $vitals[] = ['label' => __('Respiratory Rate'), 'value' => $record->respiratory_rate.' /min'];
        }
        if ($record->weight) {
            $vitals[] = ['label' => __('Weight'), 'value' => $record->weight.' kg'];
        }
        if ($record->height) {
            $vitals[] = ['label' => __('Height'), 'value' => $record->height.' cm'];
        }
        if ($record->head_circumference) {
            $vitals[] = ['label' => __('Head Circumference'), 'value' => $record->head_circumference.' cm'];
        }
        if ($record->chest_circumference) {
            $vitals[] = ['label' => __('Chest Circumference'), 'value' => $record->chest_circumference.' cm'];
        }
        if ($record->fetal_heart_tone) {
            $vitals[] = ['label' => __('Fetal Heart Tone'), 'value' => $record->fetal_heart_tone.' bpm'];
        }
        if ($record->fundal_height) {
            $vitals[] = ['label' => __('Fundal Height'), 'value' => $record->fundal_height.' cm'];
        }

        return $vitals;
    }

    public function render(): View
    {
        return view('livewire.patient.medical-record-show', [
            'record' => $this->record,
            'vitalSigns' => $this->vitalSigns,
        ])->layout('layouts.app');
    }
}
