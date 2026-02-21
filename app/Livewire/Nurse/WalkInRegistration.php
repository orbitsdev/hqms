<?php

namespace App\Livewire\Nurse;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\PersonalInformation;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class WalkInRegistration extends Component
{
    public int $currentStep = 1;

    public int $maxStep = 1;

    // Step 1: Consultation Type
    public ?int $consultationTypeId = null;

    // Step 2: Patient Information
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

    // Step 3: Chief Complaints & Visit Type
    public string $chiefComplaints = '';

    public string $visitType = 'new';

    // Step 4: Account creation (optional)
    public bool $createAccount = false;

    public string $accountEmail = '';

    public string $accountPassword = '';

    public bool $generatePassword = true;

    public function selectConsultationType(int $typeId): void
    {
        $this->consultationTypeId = $typeId;
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
            $this->validate([
                'patientFirstName' => ['required', 'string', 'max:255'],
                'patientMiddleName' => ['nullable', 'string', 'max:255'],
                'patientLastName' => ['required', 'string', 'max:255'],
                'patientDateOfBirth' => ['required', 'bail', 'date', 'before_or_equal:today'],
                'patientGender' => ['required', Rule::in(['male', 'female'])],
                'patientPhone' => ['nullable', 'string', 'max:20'],
                'patientProvince' => ['nullable', 'string', 'max:255'],
                'patientMunicipality' => ['nullable', 'string', 'max:255'],
                'patientBarangay' => ['nullable', 'string', 'max:255'],
                'patientStreet' => ['nullable', 'string', 'max:500'],
            ]);

            $this->currentStep = 3;
            $this->maxStep = max($this->maxStep, 3);

            return;
        }

        if ($this->currentStep === 3) {
            $this->validate([
                'chiefComplaints' => ['required', 'string', 'max:2000'],
            ]);

            $this->currentStep = 4;
            $this->maxStep = max($this->maxStep, 4);
        }
    }

    public function updatedCreateAccount(): void
    {
        if (! $this->createAccount) {
            $this->accountEmail = '';
            $this->accountPassword = '';
            $this->generatePassword = true;
        }
    }

    public function register(): void
    {
        $rules = [
            'consultationTypeId' => ['required', 'exists:consultation_types,id'],
            'patientFirstName' => ['required', 'string', 'max:255'],
            'patientMiddleName' => ['nullable', 'string', 'max:255'],
            'patientLastName' => ['required', 'string', 'max:255'],
            'patientDateOfBirth' => ['required', 'bail', 'date', 'before_or_equal:today'],
            'patientGender' => ['required', Rule::in(['male', 'female'])],
            'patientPhone' => ['nullable', 'string', 'max:20'],
            'patientProvince' => ['nullable', 'string', 'max:255'],
            'patientMunicipality' => ['nullable', 'string', 'max:255'],
            'patientBarangay' => ['nullable', 'string', 'max:255'],
            'patientStreet' => ['nullable', 'string', 'max:500'],
            'chiefComplaints' => ['required', 'string', 'max:2000'],
            'visitType' => ['required', Rule::in(['new', 'old', 'revisit'])],
        ];

        if ($this->createAccount) {
            $rules['accountEmail'] = ['required', 'email', 'max:255', 'unique:users,email'];

            if (! $this->generatePassword) {
                $rules['accountPassword'] = ['required', 'string', 'min:8'];
            }
        }

        $this->validate($rules);

        $nurse = Auth::user();

        if (! $nurse) {
            abort(403);
        }

        $patientUserId = $nurse->id;
        $generatedPassword = null;

        DB::transaction(function () use (&$patientUserId, &$generatedPassword): void {
            // Create patient account if requested
            if ($this->createAccount) {
                $generatedPassword = $this->generatePassword
                    ? Str::password(12)
                    : $this->accountPassword;

                $patientUser = User::create([
                    'first_name' => $this->patientFirstName,
                    'middle_name' => $this->patientMiddleName,
                    'last_name' => $this->patientLastName,
                    'email' => $this->accountEmail,
                    'phone' => $this->patientPhone,
                    'password' => Hash::make($generatedPassword),
                    'is_active' => true,
                ]);

                $patientUser->assignRole('patient');

                PersonalInformation::create([
                    'user_id' => $patientUser->id,
                    'first_name' => $this->patientFirstName,
                    'middle_name' => $this->patientMiddleName,
                    'last_name' => $this->patientLastName,
                    'date_of_birth' => $this->patientDateOfBirth,
                    'gender' => $this->patientGender,
                    'phone' => $this->patientPhone,
                    'province' => $this->patientProvince,
                    'municipality' => $this->patientMunicipality,
                    'barangay' => $this->patientBarangay,
                    'street' => $this->patientStreet,
                ]);

                $patientUserId = $patientUser->id;
            }
        });

        $appointment = Appointment::create([
            'user_id' => $patientUserId,
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
            'chief_complaints' => $this->chiefComplaints,
            'status' => 'pending',
            'source' => 'walk-in',
            'visit_type' => $this->visitType,
        ]);

        // Notify other nurses about the new walk-in
        $otherNurses = User::role('nurse')->where('id', '!=', $nurse->id)->get();

        if ($otherNurses->isNotEmpty()) {
            Notification::send($otherNurses, new GenericNotification([
                'type' => 'appointment.requested',
                'title' => __('New Walk-in Patient'),
                'message' => __(':name registered as walk-in for :type.', [
                    'name' => "{$this->patientFirstName} {$this->patientLastName}",
                    'type' => $appointment->consultationType->name,
                ]),
                'appointment_id' => $appointment->id,
                'consultation_type_id' => $appointment->consultation_type_id,
                'appointment_date' => $appointment->appointment_date,
                'sender_id' => $nurse->id,
                'sender_role' => 'nurse',
                'url' => route('nurse.appointments', ['status' => 'pending']),
            ]));
        }

        Toaster::success(__('Walk-in patient registered successfully.'));

        if ($this->createAccount && $generatedPassword) {
            Toaster::info(__('Account: :email', ['email' => $this->accountEmail]));
            Toaster::warning(__('Password: :password (copy now!)', ['password' => $generatedPassword]));
        }

        $this->redirect(route('nurse.appointments', ['status' => 'pending']), navigate: true);
    }

    public function render(): View
    {
        $consultationTypes = ConsultationType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedConsultationType = $consultationTypes->firstWhere('id', $this->consultationTypeId);

        return view('livewire.nurse.walk-in-registration', [
            'consultationTypes' => $consultationTypes,
            'selectedConsultationType' => $selectedConsultationType,
        ])->layout('layouts.app');
    }
}
