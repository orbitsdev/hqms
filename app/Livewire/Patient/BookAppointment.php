<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\SystemSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Masmerise\Toaster\Toaster;
use Livewire\Component;

class BookAppointment extends Component
{
    public int $currentStep = 1;

    public int $maxStep = 1;

    public ?int $consultationTypeId = null;

    public ?string $appointmentDate = null;

    public string $patientType = 'self';

    public string $patientRelationship = 'child';

    public string $patientFirstName = '';

    public ?string $patientMiddleName = null;

    public string $patientLastName = '';

    public ?string $patientDateOfBirth = null;

    public ?string $patientGender = null;

    public string $chiefComplaints = '';

    /** @var array<int, array<string, mixed>> */
    public array $availableDates = [];

    public function mount(): void
    {
        $this->fillSelfPatientDetails();
    }

    public function selectConsultationType(int $consultationTypeId): void
    {
        $this->consultationTypeId = $consultationTypeId;
        $this->appointmentDate = null;

        $this->availableDates = $this->buildAvailableDates();

        $this->currentStep = 2;
        $this->maxStep = 2;
    }

    public function updatedPatientType(string $value): void
    {
        if ($value === 'self') {
            $this->fillSelfPatientDetails();

            return;
        }

        $this->patientFirstName = '';
        $this->patientMiddleName = null;
        $this->patientLastName = '';
        $this->patientDateOfBirth = null;
        $this->patientGender = null;
        $this->patientRelationship = 'child';
    }

    public function selectDate(string $date): void
    {
        $this->appointmentDate = $date;
    }

    public function goToStep(int $step): void
    {
        if ($step <= $this->maxStep && $step >= 1) {
            $this->currentStep = $step;
        }
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
            $this->maxStep = max($this->maxStep, 2);

            return;
        }

        if ($this->currentStep === 2) {
            $this->validate($this->patientRules());

            $this->currentStep = 3;
            $this->maxStep = max($this->maxStep, 3);

            return;
        }

        if ($this->currentStep === 3) {
            $this->validate([
                'appointmentDate' => ['required', 'date', 'after_or_equal:today'],
            ]);

            $this->currentStep = 4;
            $this->maxStep = max($this->maxStep, 4);
        }
    }

    public function submitAppointment(): void
    {
        $this->validate(array_merge([
            'consultationTypeId' => ['required', 'exists:consultation_types,id'],
            'appointmentDate' => ['required', 'date', 'after_or_equal:today'],
            'chiefComplaints' => ['required', 'string', 'min:10', 'max:2000'],
        ], $this->patientRules()));

        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $patient = $this->resolvePatientDetails();

        Appointment::create([
            'user_id' => $user->id,
            'consultation_type_id' => $this->consultationTypeId,
            'doctor_id' => null,
            'patient_first_name' => $patient['first_name'],
            'patient_middle_name' => $patient['middle_name'],
            'patient_last_name' => $patient['last_name'],
            'patient_date_of_birth' => $patient['date_of_birth'],
            'patient_gender' => $patient['gender'],
            'patient_phone' => $patient['phone'],
            'patient_province' => $patient['province'],
            'patient_municipality' => $patient['municipality'],
            'patient_barangay' => $patient['barangay'],
            'patient_street' => $patient['street'],
            'relationship_to_account' => $patient['relationship'],
            'appointment_date' => $this->appointmentDate,
            'appointment_time' => null,
            'chief_complaints' => $this->chiefComplaints,
            'status' => 'pending',
            'source' => 'online',
        ]);

        Toaster::success(__('Appointment request submitted. We will confirm your schedule soon.'));

        $this->redirect(route('patient.appointments'), navigate: true);
    }

    /** @return array<string, array<int, string>> */
    protected function patientRules(): array
    {
        $rules = [
            'patientType' => ['required', Rule::in(['self', 'dependent'])],
        ];

        if ($this->patientType === 'dependent') {
            $rules = array_merge($rules, [
                'patientFirstName' => ['required', 'string', 'max:255'],
                'patientMiddleName' => ['nullable', 'string', 'max:255'],
                'patientLastName' => ['required', 'string', 'max:255'],
                'patientDateOfBirth' => ['required', 'date', 'before_or_equal:today'],
                'patientGender' => ['required', Rule::in(['male', 'female'])],
                'patientRelationship' => ['required', Rule::in(['child', 'spouse', 'parent', 'sibling', 'other'])],
            ]);
        }

        return $rules;
    }

    /** @return array<string, mixed> */
    protected function resolvePatientDetails(): array
    {
        $user = Auth::user();
        $info = $user?->personalInformation;

        if ($this->patientType === 'self') {
            return [
                'first_name' => $info?->first_name ?? '',
                'middle_name' => $info?->middle_name,
                'last_name' => $info?->last_name ?? '',
                'date_of_birth' => $info?->date_of_birth?->format('Y-m-d'),
                'gender' => $info?->gender,
                'phone' => $info?->phone,
                'province' => $info?->province,
                'municipality' => $info?->municipality,
                'barangay' => $info?->barangay,
                'street' => $info?->street,
                'relationship' => 'self',
            ];
        }

        return [
            'first_name' => $this->patientFirstName,
            'middle_name' => $this->patientMiddleName,
            'last_name' => $this->patientLastName,
            'date_of_birth' => $this->patientDateOfBirth,
            'gender' => $this->patientGender,
            'phone' => $info?->phone,
            'province' => $info?->province,
            'municipality' => $info?->municipality,
            'barangay' => $info?->barangay,
            'street' => $info?->street,
            'relationship' => $this->patientRelationship,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function buildAvailableDates(): array
    {
        if (! $this->consultationTypeId) {
            return [];
        }

        $type = ConsultationType::find($this->consultationTypeId);

        if (! $type) {
            return [];
        }

        $maxAdvanceDays = (int) SystemSetting::get('max_advance_booking_days', 30);
        $allowSameDay = SystemSetting::get('allow_same_day_booking', true);
        $daysToShow = min($maxAdvanceDays, 14);

        $startDate = $allowSameDay ? Carbon::today() : Carbon::today()->addDay();
        $hasSchedules = $type->doctorSchedules()->exists();

        $dates = [];

        for ($i = 0; $i < $daysToShow; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateString = $date->toDateString();

            $available = $hasSchedules ? $type->isAcceptingAppointments($dateString) : ! $date->isSunday();

            $dates[] = [
                'date' => $dateString,
                'formatted' => $date->format('M d'),
                'day_name' => $date->format('D'),
                'available' => $available,
            ];
        }

        return $dates;
    }

    protected function fillSelfPatientDetails(): void
    {
        $info = Auth::user()?->personalInformation;

        if (! $info) {
            return;
        }

        $this->patientFirstName = $info->first_name;
        $this->patientMiddleName = $info->middle_name;
        $this->patientLastName = $info->last_name;
        $this->patientDateOfBirth = $info->date_of_birth?->format('Y-m-d');
        $this->patientGender = $info->gender;
    }

    public function render(): View
    {
        $consultationTypes = ConsultationType::query()
            ->where('is_active', true)
            ->withCount('doctors')
            ->get();

        $selectedConsultation = null;
        $availableDoctors = collect();

        if ($this->consultationTypeId) {
            $selectedConsultation = ConsultationType::with('doctors')->find($this->consultationTypeId);
            $availableDoctors = $selectedConsultation?->doctors ?? collect();

            if (! $this->availableDates) {
                $this->availableDates = $this->buildAvailableDates();
            }
        }

        $selectedDate = collect($this->availableDates)->firstWhere('date', $this->appointmentDate);

        return view('livewire.patient.book-appointment', [
            'consultationTypes' => $consultationTypes,
            'selectedConsultation' => $selectedConsultation,
            'availableDoctors' => $availableDoctors,
            'availableDates' => $this->availableDates,
            'selectedDate' => $selectedDate,
        ])
            ->layout('layouts.app');
    }
}
