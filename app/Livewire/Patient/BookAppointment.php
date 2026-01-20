<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class BookAppointment extends Component
{
    public int $currentStep = 1;

    public int $maxStep = 1;

    // Step 1: Consultation Type
    public ?int $consultationTypeId = null;

    // Step 2: Date Selection
    public ?string $appointmentDate = null;

    public array $availableDates = [];

    // Step 3: Patient Details
    public string $patientType = 'self'; // self or dependent

    public string $patientFirstName = '';

    public string $patientMiddleName = '';

    public string $patientLastName = '';

    public ?string $patientDateOfBirth = null;

    public ?string $patientGender = null;

    // Step 4: Chief Complaints
    public string $chiefComplaints = '';

    // Review data
    public ?ConsultationType $consultationType = null;

    public ?array $selectedDate = null;

    protected function rules(): array
    {
        return [
            'consultationTypeId' => 'required|exists:consultation_types,id',
            'appointmentDate' => 'required|date|after_or_equal:today',
            'patientType' => ['required', Rule::in(['self', 'dependent'])],
            'chiefComplaints' => 'required|string|min:10|max:1000',
            'patientFirstName' => 'required_if:patientType,dependent|nullable|string|max:255',
            'patientMiddleName' => 'nullable|string|max:255',
            'patientLastName' => 'required_if:patientType,dependent|nullable|string|max:255',
            'patientDateOfBirth' => 'required_if:patientType,dependent|nullable|date|before:today',
            'patientGender' => ['required_if:patientType,dependent', 'nullable', Rule::in(['male', 'female'])],
        ];
    }

    public function mount(): void
    {
        $this->generateAvailableDates();
    }

    public function generateAvailableDates(): void
    {
        $this->availableDates = [];
        $startDate = Carbon::today();
        $maxDailyPatients = $this->consultationType?->max_daily_patients ?? 50;

        // Generate dates for the next 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);

            // Skip weekends (Saturday, Sunday)
            if ($date->isWeekend()) {
                continue;
            }

            $dayCapacity = $this->getDayCapacity($date);

            $slotsLeft = max($maxDailyPatients - $dayCapacity, 0);
            $isAvailable = $dayCapacity < $maxDailyPatients;

            $this->availableDates[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->format('l'),
                'formatted' => $date->format('M d, Y'),
                'capacity' => $dayCapacity,
                'max' => $maxDailyPatients,
                'available' => $isAvailable,
                'slots_left' => $slotsLeft,
                'status_text' => $isAvailable ? $slotsLeft.' slots left' : 'Fully booked',
                'status_class' => $isAvailable
                    ? 'text-zinc-600 dark:text-zinc-300'
                    : 'text-zinc-500 dark:text-zinc-400',
                'button_class' => $isAvailable ? '' : 'opacity-50 cursor-not-allowed',
                'button_disabled' => $isAvailable ? '' : 'disabled',
            ];
        }
    }

    private function getDayCapacity(Carbon $date): int
    {
        if (! $this->consultationTypeId) {
            return 0;
        }

        return Appointment::query()
            ->where('consultation_type_id', $this->consultationTypeId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->count();
    }

    public function selectConsultationType(int $typeId): void
    {
        $this->consultationTypeId = $typeId;
        $this->consultationType = ConsultationType::find($typeId);
        $this->generateAvailableDates();
        $this->currentStep = 2;
        $this->maxStep = max($this->maxStep, 2);
    }

    public function selectDate(string $date): void
    {
        $this->appointmentDate = $date;
        $this->selectedDate = collect($this->availableDates)->firstWhere('date', $date);
        $this->currentStep = 3;
        $this->maxStep = max($this->maxStep, 3);
    }

    public function nextStep(): void
    {
        $this->validateStep($this->currentStep);
        $this->currentStep++;
        $this->maxStep = max($this->maxStep, $this->currentStep);
    }

    public function previousStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > $this->maxStep) {
            return;
        }

        $this->currentStep = $step;
    }

    private function validateStep(int $step): void
    {
        switch ($step) {
            case 1:
                $this->validate(['consultationTypeId' => 'required|exists:consultation_types,id']);
                break;
            case 2:
                $this->validate(['appointmentDate' => 'required|date|after_or_equal:today']);
                break;
            case 3:
                $this->validate([
                    'patientType' => ['required', Rule::in(['self', 'dependent'])],
                    'patientFirstName' => 'required_if:patientType,dependent|nullable|string|max:255',
                    'patientMiddleName' => 'nullable|string|max:255',
                    'patientLastName' => 'required_if:patientType,dependent|nullable|string|max:255',
                    'patientDateOfBirth' => 'required_if:patientType,dependent|nullable|date|before:today',
                    'patientGender' => ['required_if:patientType,dependent', 'nullable', Rule::in(['male', 'female'])],
                ]);
                break;
            case 4:
                $this->validate(['chiefComplaints' => 'required|string|min:10|max:1000']);
                break;
        }
    }

    public function submitAppointment(): void
    {
        $this->validate();

        $user = Auth::user();
        $personalInfo = $user->personalInformation;

        // Determine patient info based on patient type
        if ($this->patientType === 'self') {
            $patientFirstName = $personalInfo?->first_name ?? '';
            $patientMiddleName = $personalInfo?->middle_name;
            $patientLastName = $personalInfo?->last_name ?? '';
            $patientDateOfBirth = $personalInfo?->date_of_birth;
            $patientGender = $personalInfo?->gender;
        } else {
            $patientFirstName = $this->patientFirstName;
            $patientMiddleName = $this->patientMiddleName ?: null;
            $patientLastName = $this->patientLastName;
            $patientDateOfBirth = $this->patientDateOfBirth;
            $patientGender = $this->patientGender;
        }

        DB::transaction(function () use ($user, $personalInfo, $patientFirstName, $patientMiddleName, $patientLastName, $patientDateOfBirth, $patientGender) {
            // Create the appointment
            $appointment = Appointment::create([
                'user_id' => $user->id,
                'consultation_type_id' => $this->consultationTypeId,
                'appointment_date' => $this->appointmentDate,
                'status' => 'pending',
                'chief_complaints' => $this->chiefComplaints,
                'patient_type' => $this->patientType,
                'patient_first_name' => $patientFirstName,
                'patient_middle_name' => $patientMiddleName,
                'patient_last_name' => $patientLastName,
                'patient_date_of_birth' => $patientDateOfBirth,
                'patient_gender' => $patientGender,
            ]);

            // Create pre-visit medical record
            MedicalRecord::create([
                'user_id' => $user->id,
                'consultation_type_id' => $this->consultationTypeId,
                'appointment_id' => $appointment->id,
                'visit_date' => $this->appointmentDate,
                'visit_type' => 'new',
                'service_type' => 'checkup',
                'status' => 'in_progress',
                'is_pre_visit' => true,
                'chief_complaints_initial' => $this->chiefComplaints,
                // Patient info
                'patient_first_name' => $patientFirstName,
                'patient_middle_name' => $patientMiddleName,
                'patient_last_name' => $patientLastName,
                'patient_date_of_birth' => $patientDateOfBirth,
                'patient_gender' => $patientGender,
                // Copy address from account owner's personal info
                'patient_province' => $personalInfo?->province,
                'patient_municipality' => $personalInfo?->municipality,
                'patient_barangay' => $personalInfo?->barangay,
                'patient_street' => $personalInfo?->street,
                'patient_contact_number' => $personalInfo?->phone,
                'emergency_contact_name' => $personalInfo?->emergency_contact_name,
                'emergency_contact_phone' => $personalInfo?->emergency_contact_phone,
            ]);
        });

        // In real app, send SMS notification here
        $this->dispatch('appointmentBooked', 'Appointment submitted successfully! You will receive an SMS confirmation.');

        // Redirect to appointments list
        $this->redirect(route('patient.appointments'), navigate: true);
    }

    public function render(): View
    {
        $consultationTypes = ConsultationType::where('is_active', true)->get();

        return view('livewire.patient.book-appointment', [
            'consultationTypes' => $consultationTypes,
        ])->layout('layouts.app');
    }
}
