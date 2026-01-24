<?php

namespace App\Livewire\Nurse;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class WalkInRegistration extends Component
{
    public ?int $consultationTypeId = null;

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

    public function register(): void
    {
        $this->validate([
            'consultationTypeId' => ['required', 'exists:consultation_types,id'],
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
            'chiefComplaints' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $nurse = Auth::user();

        if (! $nurse) {
            abort(403);
        }

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
            'chief_complaints' => $this->chiefComplaints,
            'status' => 'pending',
            'source' => 'walk-in',
        ]);

        $nurses = User::role('nurse')->get();

        Notification::send($nurses, new GenericNotification([
            'type' => 'appointment.requested',
            'title' => 'New Walk-in Patient',
            'message' => "{$this->patientFirstName} {$this->patientLastName} registered as walk-in.",
            'appointment_id' => $appointment->id,
            'consultation_type_id' => $appointment->consultation_type_id,
            'appointment_date' => $appointment->appointment_date,
            'sender_id' => $nurse->id,
            'sender_role' => 'nurse',
            'url' => route('nurse.appointments.show', $appointment),
        ]));

        Toaster::success(__('Walk-in patient registered. Please approve to add to queue.'));

        $this->redirect(route('nurse.appointments.show', $appointment), navigate: true);
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
