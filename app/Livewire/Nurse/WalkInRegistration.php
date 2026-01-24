<?php

namespace App\Livewire\Nurse;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\Queue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class WalkInRegistration extends Component
{
    public int $currentStep = 1;

    public ?int $consultationTypeId = null;

    public string $patientType = 'new';

    public string $patientFirstName = '';

    public ?string $patientMiddleName = null;

    public string $patientLastName = '';

    public ?string $patientDateOfBirth = null;

    public ?string $patientGender = null;

    public ?string $patientPhone = null;

    public ?string $patientProvince = null;

    public ?string $patientMunicipality = null;

    public ?string $patientBarangay = null;

    public ?string $patientStreet = null;

    public string $chiefComplaints = '';

    public string $priority = 'normal';

    public function selectConsultationType(int $id): void
    {
        $this->consultationTypeId = $id;
        $this->currentStep = 2;
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'consultationTypeId' => ['required', 'exists:consultation_types,id'],
            ]);
            $this->currentStep = 2;

            return;
        }

        if ($this->currentStep === 2) {
            $this->validate($this->patientRules());
            $this->currentStep = 3;
        }
    }

    public function register(): void
    {
        $this->validate(array_merge([
            'consultationTypeId' => ['required', 'exists:consultation_types,id'],
            'chiefComplaints' => ['required', 'string', 'min:5', 'max:2000'],
            'priority' => ['required', Rule::in(['normal', 'urgent', 'emergency'])],
        ], $this->patientRules()));

        $nurse = Auth::user();

        if (! $nurse) {
            abort(403);
        }

        DB::transaction(function () use ($nurse): void {
            $consultationType = ConsultationType::find($this->consultationTypeId);

            $appointment = Appointment::create([
                'user_id' => $nurse->id,
                'consultation_type_id' => $this->consultationTypeId,
                'doctor_id' => null,
                'patient_first_name' => $this->patientFirstName,
                'patient_middle_name' => $this->patientMiddleName,
                'patient_last_name' => $this->patientLastName,
                'patient_date_of_birth' => $this->patientDateOfBirth,
                'patient_gender' => $this->patientGender,
                'patient_phone' => $this->patientPhone,
                'patient_province' => $this->patientProvince,
                'patient_municipality' => $this->patientMunicipality,
                'patient_barangay' => $this->patientBarangay,
                'patient_street' => $this->patientStreet,
                'relationship_to_account' => 'self',
                'appointment_date' => today(),
                'appointment_time' => now()->format('H:i'),
                'chief_complaints' => $this->chiefComplaints,
                'status' => 'checked_in',
                'checked_in_at' => now(),
                'approved_by' => $nurse->id,
                'approved_at' => now(),
                'source' => 'walk-in',
            ]);

            $queueNumber = $this->generateQueueNumber();

            Queue::create([
                'appointment_id' => $appointment->id,
                'user_id' => $nurse->id,
                'consultation_type_id' => $this->consultationTypeId,
                'doctor_id' => null,
                'queue_number' => $queueNumber,
                'queue_date' => today(),
                'session_number' => 1,
                'priority' => $this->priority,
                'status' => 'waiting',
                'source' => 'walk-in',
            ]);
        });

        Toaster::success(__('Walk-in patient registered successfully.'));

        $this->redirect(route('nurse.queue'), navigate: true);
    }

    /** @return array<string, array<int, mixed>> */
    protected function patientRules(): array
    {
        return [
            'patientFirstName' => ['required', 'string', 'max:255'],
            'patientMiddleName' => ['nullable', 'string', 'max:255'],
            'patientLastName' => ['required', 'string', 'max:255'],
            'patientDateOfBirth' => ['required', 'date', 'before_or_equal:today'],
            'patientGender' => ['required', Rule::in(['male', 'female'])],
            'patientPhone' => ['nullable', 'string', 'max:20'],
            'patientProvince' => ['nullable', 'string', 'max:255'],
            'patientMunicipality' => ['nullable', 'string', 'max:255'],
            'patientBarangay' => ['nullable', 'string', 'max:255'],
            'patientStreet' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function generateQueueNumber(): int
    {
        $lastQueue = Queue::query()
            ->where('consultation_type_id', $this->consultationTypeId)
            ->where('queue_date', today())
            ->where('session_number', 1)
            ->max('queue_number');

        return ($lastQueue ?? 0) + 1;
    }

    public function render(): View
    {
        $consultationTypes = ConsultationType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.nurse.walk-in-registration', [
            'consultationTypes' => $consultationTypes,
        ])->layout('layouts.app');
    }
}
