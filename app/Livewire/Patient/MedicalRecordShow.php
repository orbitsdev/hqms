<?php

namespace App\Livewire\Patient;

use App\Models\MedicalRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class MedicalRecordShow extends Component
{
    public $medicalRecord;
    public $prescriptions;
    public $vitalSigns;

    public function mount(MedicalRecord $medicalRecord): void
    {
        $this->medicalRecord = $medicalRecord->load([
            'consultationType',
            'prescriptions',
            'billingTransaction.billingItems',
            'queue',
        ]);

        if ($this->medicalRecord->user_id !== Auth::id()) {
            abort(403);
        }

        $this->prescriptions = $this->medicalRecord->prescriptions;
        $this->vitalSigns = array_filter([
            'temperature' => $this->medicalRecord->temperature,
            'blood_pressure' => $this->medicalRecord->blood_pressure,
            'cardiac_rate' => $this->medicalRecord->cardiac_rate,
            'respiratory_rate' => $this->medicalRecord->respiratory_rate,
            'weight' => $this->medicalRecord->weight,
            'height' => $this->medicalRecord->height,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public function downloadPDF(): void
    {
        // In real app, generate PDF here
        $this->dispatch('download', 'PDF download feature coming soon!');
    }

    public function shareRecord(): void
    {
        // In real app, generate shareable link or send email
        $this->dispatch('share', 'Record sharing feature coming soon!');
    }

    public function render(): View
    {
        return view('livewire.patient.medical-record-show')
            ->layout('layouts.app');
    }
}
