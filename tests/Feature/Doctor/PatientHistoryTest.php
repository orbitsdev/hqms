<?php

use App\Livewire\Doctor\PatientHistory;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\PersonalInformation;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::findOrCreate('doctor', 'web');
    Role::findOrCreate('nurse', 'web');
    Role::findOrCreate('patient', 'web');

    $this->doctor = User::factory()->create();
    $this->doctor->assignRole('doctor');

    PersonalInformation::factory()->create([
        'user_id' => $this->doctor->id,
        'first_name' => 'Test',
        'last_name' => 'Doctor',
    ]);

    $this->consultationType = ConsultationType::factory()->create([
        'code' => 'ped',
        'name' => 'Pediatrics',
        'short_name' => 'P',
    ]);

    $this->doctor->consultationTypes()->attach($this->consultationType);
});

// ==================== ACCESS TESTS ====================

it('renders the patient history page for doctors', function () {
    actingAs($this->doctor)
        ->get(route('doctor.patient-history'))
        ->assertSuccessful()
        ->assertSee('Patient History');
});

it('denies access to non-doctors', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('doctor.patient-history'))
        ->assertForbidden();
});

// ==================== DISPLAY TESTS ====================

it('displays completed medical records from doctors consultation types', function () {
    MedicalRecord::factory()->completed()->create([
        'consultation_type_id' => $this->consultationType->id,
        'doctor_id' => $this->doctor->id,
        'patient_first_name' => 'John',
        'patient_last_name' => 'Doe',
        'diagnosis' => 'Test Diagnosis',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(PatientHistory::class)
        ->assertSee('John')
        ->assertSee('Doe')
        ->assertSee('Test Diagnosis');
});

it('displays records where doctor was the examiner', function () {
    // Create a separate consultation type that the doctor is not attached to
    $otherType = ConsultationType::factory()->create([
        'code' => 'gen',
        'name' => 'General',
        'short_name' => 'G',
    ]);

    // Even without matching consultation type, doctor should see their own records
    MedicalRecord::factory()->completed()->create([
        'consultation_type_id' => $otherType->id,
        'doctor_id' => $this->doctor->id,
        'patient_first_name' => 'Direct',
        'patient_last_name' => 'Patient',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(PatientHistory::class)
        ->assertSee('Direct')
        ->assertSee('Patient');
});

it('displays records with for_billing status', function () {
    MedicalRecord::factory()->examined()->create([
        'consultation_type_id' => $this->consultationType->id,
        'doctor_id' => $this->doctor->id,
        'patient_first_name' => 'Billing',
        'patient_last_name' => 'Patient',
        'status' => 'for_billing',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(PatientHistory::class)
        ->assertSee('Billing')
        ->assertSee('Patient');
});

it('displays records with for_admission status', function () {
    MedicalRecord::factory()->examined()->create([
        'consultation_type_id' => $this->consultationType->id,
        'doctor_id' => $this->doctor->id,
        'patient_first_name' => 'Admission',
        'patient_last_name' => 'Patient',
        'status' => 'for_admission',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(PatientHistory::class)
        ->assertSee('Admission')
        ->assertSee('Patient');
});

it('does not display in_progress records', function () {
    MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'doctor_id' => $this->doctor->id,
        'patient_first_name' => 'InProgress',
        'patient_last_name' => 'Patient',
        'status' => 'in_progress',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(PatientHistory::class)
        ->assertDontSee('InProgress');
});

// ==================== SEARCH TESTS ====================

it('can search records by patient first name', function () {
    MedicalRecord::factory()->completed()->create([
        'consultation_type_id' => $this->consultationType->id,
        'doctor_id' => $this->doctor->id,
        'patient_first_name' => 'Maria',
        'patient_last_name' => 'Santos',
    ]);

    MedicalRecord::factory()->completed()->create([
        'consultation_type_id' => $this->consultationType->id,
        'doctor_id' => $this->doctor->id,
        'patient_first_name' => 'Jose',
        'patient_last_name' => 'Cruz',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(PatientHistory::class)
        ->set('search', 'Maria')
        ->assertSee('Maria')
        ->assertDontSee('Jose');
});

it('can search records by diagnosis', function () {
    MedicalRecord::factory()->completed()->create([
        'consultation_type_id' => $this->consultationType->id,
        'doctor_id' => $this->doctor->id,
        'patient_first_name' => 'DiabetesPatient',
        'patient_last_name' => 'Test',
        'diagnosis' => 'Type 2 Diabetes Mellitus',
    ]);

    MedicalRecord::factory()->completed()->create([
        'consultation_type_id' => $this->consultationType->id,
        'doctor_id' => $this->doctor->id,
        'patient_first_name' => 'ColdPatient',
        'patient_last_name' => 'Test',
        'diagnosis' => 'Common Cold',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(PatientHistory::class)
        ->set('search', 'Diabetes')
        ->assertSee('DiabetesPatient')
        ->assertDontSee('ColdPatient');
});

// ==================== VIEW DETAIL TESTS ====================

it('can view record detail modal', function () {
    $record = MedicalRecord::factory()->completed()->create([
        'consultation_type_id' => $this->consultationType->id,
        'doctor_id' => $this->doctor->id,
    ]);

    Livewire::actingAs($this->doctor)
        ->test(PatientHistory::class)
        ->assertSet('showDetailModal', false)
        ->call('viewRecord', $record->id)
        ->assertSet('showDetailModal', true)
        ->assertSet('selectedRecordId', $record->id)
        ->call('closeDetailModal')
        ->assertSet('showDetailModal', false)
        ->assertSet('selectedRecordId', null);
});
