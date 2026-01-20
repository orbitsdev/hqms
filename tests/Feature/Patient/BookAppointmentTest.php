<?php

use App\Livewire\Patient\BookAppointment;
use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\PersonalInformation;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->consultationType = ConsultationType::factory()->create();
    $this->user = User::factory()->create();
    $this->personalInfo = PersonalInformation::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

it('renders the book appointment page', function () {
    $this->actingAs($this->user)
        ->get(route('patient.appointments.book'))
        ->assertOk()
        ->assertSeeLivewire(BookAppointment::class);
});

it('can select consultation type and move to step 2', function () {
    Livewire::actingAs($this->user)
        ->test(BookAppointment::class)
        ->assertSet('currentStep', 1)
        ->call('selectConsultationType', $this->consultationType->id)
        ->assertSet('currentStep', 2)
        ->assertSet('consultationTypeId', $this->consultationType->id);
});

it('can select date and move to step 3', function () {
    $futureDate = now()->addDays(1)->format('Y-m-d');

    // Skip if the date is a weekend
    while (now()->parse($futureDate)->isWeekend()) {
        $futureDate = now()->parse($futureDate)->addDay()->format('Y-m-d');
    }

    Livewire::actingAs($this->user)
        ->test(BookAppointment::class)
        ->call('selectConsultationType', $this->consultationType->id)
        ->call('selectDate', $futureDate)
        ->assertSet('currentStep', 3)
        ->assertSet('appointmentDate', $futureDate);
});

it('can submit appointment for self', function () {
    $futureDate = now()->addDays(1)->format('Y-m-d');

    // Skip if the date is a weekend
    while (now()->parse($futureDate)->isWeekend()) {
        $futureDate = now()->parse($futureDate)->addDay()->format('Y-m-d');
    }

    Livewire::actingAs($this->user)
        ->test(BookAppointment::class)
        ->call('selectConsultationType', $this->consultationType->id)
        ->call('selectDate', $futureDate)
        ->set('patientType', 'self')
        ->call('nextStep')
        ->assertSet('currentStep', 4)
        ->set('chiefComplaints', 'I have been experiencing severe headaches for the past week.')
        ->call('submitAppointment')
        ->assertRedirect(route('patient.appointments'));

    // Verify appointment was created
    $appointment = \App\Models\Appointment::where('user_id', $this->user->id)->first();
    expect($appointment)->not->toBeNull();
    expect($appointment->consultation_type_id)->toBe($this->consultationType->id);
    expect($appointment->appointment_date->format('Y-m-d'))->toBe($futureDate);
    expect($appointment->patient_type)->toBe('self');
    expect($appointment->patient_first_name)->toBe($this->personalInfo->first_name);
    expect($appointment->patient_last_name)->toBe($this->personalInfo->last_name);
    expect($appointment->status)->toBe('pending');

    // Verify medical record was created
    $this->assertDatabaseHas('medical_records', [
        'user_id' => $this->user->id,
        'consultation_type_id' => $this->consultationType->id,
        'is_pre_visit' => true,
        'patient_first_name' => $this->personalInfo->first_name,
        'patient_last_name' => $this->personalInfo->last_name,
    ]);
});

it('can submit appointment for dependent', function () {
    $futureDate = now()->addDays(1)->format('Y-m-d');

    // Skip if the date is a weekend
    while (now()->parse($futureDate)->isWeekend()) {
        $futureDate = now()->parse($futureDate)->addDay()->format('Y-m-d');
    }

    $dependentFirstName = 'Maria';
    $dependentLastName = 'Santos';
    $dependentBirthDate = '2020-05-15';

    Livewire::actingAs($this->user)
        ->test(BookAppointment::class)
        ->call('selectConsultationType', $this->consultationType->id)
        ->call('selectDate', $futureDate)
        ->set('patientType', 'dependent')
        ->set('patientFirstName', $dependentFirstName)
        ->set('patientMiddleName', 'Cruz')
        ->set('patientLastName', $dependentLastName)
        ->set('patientDateOfBirth', $dependentBirthDate)
        ->set('patientGender', 'female')
        ->call('nextStep')
        ->assertSet('currentStep', 4)
        ->set('chiefComplaints', 'My child has a fever and cough for 3 days.')
        ->call('submitAppointment')
        ->assertRedirect(route('patient.appointments'));

    // Verify appointment was created with dependent info
    $appointment = \App\Models\Appointment::where('user_id', $this->user->id)->first();
    expect($appointment)->not->toBeNull();
    expect($appointment->consultation_type_id)->toBe($this->consultationType->id);
    expect($appointment->appointment_date->format('Y-m-d'))->toBe($futureDate);
    expect($appointment->patient_type)->toBe('dependent');
    expect($appointment->patient_first_name)->toBe($dependentFirstName);
    expect($appointment->patient_last_name)->toBe($dependentLastName);
    expect((string) $appointment->patient_date_of_birth)->toContain('2020-05-15');
    expect($appointment->patient_gender)->toBe('female');
    expect($appointment->status)->toBe('pending');

    // Verify medical record was created with dependent info
    $medicalRecord = \App\Models\MedicalRecord::where('appointment_id', $appointment->id)->first();
    expect($medicalRecord)->not->toBeNull();
    expect($medicalRecord->user_id)->toBe($this->user->id);
    expect($medicalRecord->consultation_type_id)->toBe($this->consultationType->id);
    expect($medicalRecord->is_pre_visit)->toBeTrue();
    expect($medicalRecord->patient_first_name)->toBe($dependentFirstName);
    expect($medicalRecord->patient_last_name)->toBe($dependentLastName);
    expect($medicalRecord->patient_date_of_birth->format('Y-m-d'))->toBe($dependentBirthDate);
    expect($medicalRecord->patient_gender)->toBe('female');
});

it('validates required fields for dependent', function () {
    $futureDate = now()->addDays(1)->format('Y-m-d');

    while (now()->parse($futureDate)->isWeekend()) {
        $futureDate = now()->parse($futureDate)->addDay()->format('Y-m-d');
    }

    Livewire::actingAs($this->user)
        ->test(BookAppointment::class)
        ->call('selectConsultationType', $this->consultationType->id)
        ->call('selectDate', $futureDate)
        ->set('patientType', 'dependent')
        ->set('patientFirstName', '')
        ->set('patientLastName', '')
        ->set('patientDateOfBirth', null)
        ->set('patientGender', null)
        ->call('nextStep')
        ->assertHasErrors(['patientFirstName', 'patientLastName', 'patientDateOfBirth', 'patientGender']);
});

it('validates chief complaints minimum length', function () {
    $futureDate = now()->addDays(1)->format('Y-m-d');

    while (now()->parse($futureDate)->isWeekend()) {
        $futureDate = now()->parse($futureDate)->addDay()->format('Y-m-d');
    }

    Livewire::actingAs($this->user)
        ->test(BookAppointment::class)
        ->call('selectConsultationType', $this->consultationType->id)
        ->call('selectDate', $futureDate)
        ->set('patientType', 'self')
        ->call('nextStep')
        ->set('chiefComplaints', 'Short')
        ->call('submitAppointment')
        ->assertHasErrors(['chiefComplaints']);
});

it('links medical record to appointment', function () {
    $futureDate = now()->addDays(1)->format('Y-m-d');

    while (now()->parse($futureDate)->isWeekend()) {
        $futureDate = now()->parse($futureDate)->addDay()->format('Y-m-d');
    }

    Livewire::actingAs($this->user)
        ->test(BookAppointment::class)
        ->call('selectConsultationType', $this->consultationType->id)
        ->call('selectDate', $futureDate)
        ->set('patientType', 'self')
        ->call('nextStep')
        ->set('chiefComplaints', 'Testing that medical record is linked to appointment.')
        ->call('submitAppointment');

    $appointment = Appointment::where('user_id', $this->user->id)->first();
    $medicalRecord = MedicalRecord::where('appointment_id', $appointment->id)->first();

    expect($appointment)->not->toBeNull();
    expect($medicalRecord)->not->toBeNull();
    expect($medicalRecord->appointment_id)->toBe($appointment->id);
    expect($medicalRecord->is_pre_visit)->toBeTrue();
});
