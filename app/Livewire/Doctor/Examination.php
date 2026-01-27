<?php

namespace App\Livewire\Doctor;

use App\Models\HospitalDrug;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class Examination extends Component
{
    #[Locked]
    public int $medicalRecordId;

    // Doctor's examination fields
    #[Validate('nullable|string|max:5000')]
    public string $pertinentHpiPe = '';

    #[Validate('nullable|string|max:2000')]
    public string $diagnosis = '';

    #[Validate('nullable|string|max:2000')]
    public string $plan = '';

    #[Validate('nullable|string|max:2000')]
    public string $proceduresDone = '';

    #[Validate('nullable|string|max:2000')]
    public string $prescriptionNotes = '';

    // Discount recommendation
    public string $suggestedDiscountType = 'none';

    public string $suggestedDiscountReason = '';

    // Prescription modal
    public bool $showPrescriptionModal = false;

    public ?int $editingPrescriptionId = null;

    #[Validate('required|string|max:255')]
    public string $medicationName = '';

    #[Validate('nullable|string|max:100')]
    public string $dosage = '';

    #[Validate('nullable|string|max:100')]
    public string $frequency = '';

    #[Validate('nullable|string|max:100')]
    public string $duration = '';

    #[Validate('nullable|integer|min:1')]
    public ?int $quantity = null;

    #[Validate('nullable|string|max:500')]
    public string $instructions = '';

    public bool $isHospitalDrug = false;

    public ?int $hospitalDrugId = null;

    public string $drugSearch = '';

    // Completion modal
    public bool $showCompleteModal = false;

    public string $completionAction = 'for_billing';

    // Patient history modal
    public bool $showHistoryModal = false;

    public function mount(MedicalRecord $medicalRecord): void
    {
        // Authorization check
        $doctor = Auth::user();

        if ($medicalRecord->doctor_id !== null && $medicalRecord->doctor_id !== $doctor->id) {
            abort(403, 'This patient is being examined by another doctor.');
        }

        // Check if patient is in valid state
        if ($medicalRecord->status !== 'in_progress') {
            Toaster::error(__('This patient has already been processed.'));
            $this->redirect(route('doctor.queue'), navigate: true);

            return;
        }

        $this->medicalRecordId = $medicalRecord->id;

        // Load existing data
        $this->pertinentHpiPe = $medicalRecord->pertinent_hpi_pe ?? '';
        $this->diagnosis = $medicalRecord->diagnosis ?? '';
        $this->plan = $medicalRecord->plan ?? '';
        $this->proceduresDone = $medicalRecord->procedures_done ?? '';
        $this->prescriptionNotes = $medicalRecord->prescription_notes ?? '';
        $this->suggestedDiscountType = $medicalRecord->suggested_discount_type ?? 'none';
        $this->suggestedDiscountReason = $medicalRecord->suggested_discount_reason ?? '';

        // Mark as being examined if not already
        if (! $medicalRecord->examined_at) {
            $medicalRecord->update([
                'doctor_id' => $doctor->id,
                'examined_at' => now(),
                'examination_time' => now()->format('A') === 'AM' ? 'am' : 'pm',
            ]);
        }
    }

    #[Computed]
    public function medicalRecord(): MedicalRecord
    {
        return MedicalRecord::with([
            'prescriptions.hospitalDrug',
            'consultationType',
            'nurse',
            'queue',
            'appointment',
        ])->findOrFail($this->medicalRecordId);
    }

    #[Computed]
    public function patientHistory(): \Illuminate\Support\Collection
    {
        $record = $this->medicalRecord;

        return MedicalRecord::query()
            ->with(['consultationType', 'doctor', 'prescriptions'])
            ->where('patient_first_name', $record->patient_first_name)
            ->where('patient_last_name', $record->patient_last_name)
            ->when($record->patient_date_of_birth, fn ($q) => $q->where('patient_date_of_birth', $record->patient_date_of_birth))
            ->where('id', '!=', $record->id)
            ->whereIn('status', ['for_billing', 'for_admission', 'completed'])
            ->orderByDesc('visit_date')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function hospitalDrugs(): \Illuminate\Support\Collection
    {
        if (strlen($this->drugSearch) < 2) {
            return collect();
        }

        return HospitalDrug::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('drug_name', 'like', '%'.$this->drugSearch.'%')
                    ->orWhere('generic_name', 'like', '%'.$this->drugSearch.'%');
            })
            ->limit(10)
            ->get();
    }

    public function saveDraft(): void
    {
        $this->validate([
            'pertinentHpiPe' => 'nullable|string|max:5000',
            'diagnosis' => 'nullable|string|max:2000',
            'plan' => 'nullable|string|max:2000',
            'proceduresDone' => 'nullable|string|max:2000',
            'prescriptionNotes' => 'nullable|string|max:2000',
        ]);

        $this->medicalRecord->update([
            'pertinent_hpi_pe' => $this->pertinentHpiPe ?: null,
            'diagnosis' => $this->diagnosis ?: null,
            'plan' => $this->plan ?: null,
            'procedures_done' => $this->proceduresDone ?: null,
            'prescription_notes' => $this->prescriptionNotes ?: null,
            'suggested_discount_type' => $this->suggestedDiscountType,
            'suggested_discount_reason' => $this->suggestedDiscountReason ?: null,
        ]);

        Toaster::success(__('Draft saved.'));
    }

    // Prescription methods
    public function openPrescriptionModal(): void
    {
        $this->resetPrescriptionForm();
        $this->showPrescriptionModal = true;
    }

    public function closePrescriptionModal(): void
    {
        $this->showPrescriptionModal = false;
        $this->resetPrescriptionForm();
    }

    public function resetPrescriptionForm(): void
    {
        $this->editingPrescriptionId = null;
        $this->medicationName = '';
        $this->dosage = '';
        $this->frequency = '';
        $this->duration = '';
        $this->quantity = null;
        $this->instructions = '';
        $this->isHospitalDrug = false;
        $this->hospitalDrugId = null;
        $this->drugSearch = '';
        $this->resetValidation();
    }

    public function selectHospitalDrug(int $drugId): void
    {
        $drug = HospitalDrug::find($drugId);

        if ($drug) {
            $this->hospitalDrugId = $drug->id;
            $this->medicationName = $drug->drug_name;
            $this->isHospitalDrug = true;
            $this->drugSearch = '';
        }
    }

    public function clearHospitalDrug(): void
    {
        $this->hospitalDrugId = null;
        $this->isHospitalDrug = false;
    }

    public function savePrescription(): void
    {
        $this->validate([
            'medicationName' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:100',
            'frequency' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:100',
            'quantity' => 'nullable|integer|min:1',
            'instructions' => 'nullable|string|max:500',
        ]);

        $data = [
            'medical_record_id' => $this->medicalRecordId,
            'prescribed_by' => Auth::id(),
            'medication_name' => $this->medicationName,
            'dosage' => $this->dosage ?: null,
            'frequency' => $this->frequency ?: null,
            'duration' => $this->duration ?: null,
            'quantity' => $this->quantity,
            'instructions' => $this->instructions ?: null,
            'is_hospital_drug' => $this->isHospitalDrug,
            'hospital_drug_id' => $this->isHospitalDrug ? $this->hospitalDrugId : null,
        ];

        if ($this->editingPrescriptionId) {
            Prescription::where('id', $this->editingPrescriptionId)->update($data);
            Toaster::success(__('Prescription updated.'));
        } else {
            Prescription::create($data);
            Toaster::success(__('Prescription added.'));
        }

        $this->closePrescriptionModal();
    }

    public function editPrescription(int $id): void
    {
        $prescription = Prescription::find($id);

        if (! $prescription || $prescription->medical_record_id !== $this->medicalRecordId) {
            return;
        }

        $this->editingPrescriptionId = $id;
        $this->medicationName = $prescription->medication_name;
        $this->dosage = $prescription->dosage ?? '';
        $this->frequency = $prescription->frequency ?? '';
        $this->duration = $prescription->duration ?? '';
        $this->quantity = $prescription->quantity;
        $this->instructions = $prescription->instructions ?? '';
        $this->isHospitalDrug = $prescription->is_hospital_drug;
        $this->hospitalDrugId = $prescription->hospital_drug_id;

        $this->showPrescriptionModal = true;
    }

    public function deletePrescription(int $id): void
    {
        $prescription = Prescription::find($id);

        if ($prescription && $prescription->medical_record_id === $this->medicalRecordId) {
            $prescription->delete();
            Toaster::success(__('Prescription removed.'));
        }
    }

    // Completion methods
    public function openCompleteModal(): void
    {
        if (empty(trim($this->diagnosis))) {
            Toaster::error(__('Please enter a diagnosis before completing.'));

            return;
        }

        $this->showCompleteModal = true;
    }

    public function closeCompleteModal(): void
    {
        $this->showCompleteModal = false;
    }

    public function completeExamination(): void
    {
        $this->validate([
            'pertinentHpiPe' => 'nullable|string|max:5000',
            'diagnosis' => 'required|string|max:2000',
            'plan' => 'nullable|string|max:2000',
            'proceduresDone' => 'nullable|string|max:2000',
            'prescriptionNotes' => 'nullable|string|max:2000',
            'completionAction' => 'required|in:for_billing,for_admission,completed',
        ]);

        DB::transaction(function (): void {
            $this->medicalRecord->update([
                'pertinent_hpi_pe' => $this->pertinentHpiPe ?: null,
                'diagnosis' => $this->diagnosis,
                'plan' => $this->plan ?: null,
                'procedures_done' => $this->proceduresDone ?: null,
                'prescription_notes' => $this->prescriptionNotes ?: null,
                'suggested_discount_type' => $this->suggestedDiscountType,
                'suggested_discount_reason' => $this->suggestedDiscountReason ?: null,
                'examination_ended_at' => now(),
                'status' => $this->completionAction,
            ]);

            // Notify patient if they have an account
            $patientUser = $this->medicalRecord->user;
            if ($patientUser && $patientUser->hasRole('patient')) {
                $statusMessage = match ($this->completionAction) {
                    'for_billing' => __('Please proceed to the cashier for billing.'),
                    'for_admission' => __('You have been recommended for admission.'),
                    default => __('Your consultation is complete.'),
                };

                $patientUser->notify(new GenericNotification([
                    'type' => 'examination.completed',
                    'title' => __('Consultation Completed'),
                    'message' => $statusMessage,
                    'medical_record_id' => $this->medicalRecord->id,
                    'sender_id' => Auth::id(),
                    'sender_role' => 'doctor',
                ]));
            }
        });

        $this->closeCompleteModal();

        Toaster::success(__('Examination completed.'));

        $this->redirect(route('doctor.queue'), navigate: true);
    }

    public function openHistoryModal(): void
    {
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal(): void
    {
        $this->showHistoryModal = false;
    }

    public function render(): View
    {
        return view('livewire.doctor.examination', [
            'record' => $this->medicalRecord,
            'prescriptions' => $this->medicalRecord->prescriptions,
            'history' => $this->patientHistory,
            'drugs' => $this->hospitalDrugs,
        ])->layout('layouts.app');
    }
}
