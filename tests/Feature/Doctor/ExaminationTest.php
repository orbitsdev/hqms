<?php

use App\Livewire\Doctor\Examination;
use App\Models\Admission;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\PersonalInformation;
use App\Models\Prescription;
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

it('renders the examination page for doctors', function () {
    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    actingAs($this->doctor)
        ->get(route('doctor.examine', $record))
        ->assertSuccessful()
        ->assertSee('Examination');
});

it('denies access to non-doctors', function () {
    $user = User::factory()->create();

    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
    ]);

    actingAs($user)
        ->get(route('doctor.examine', $record))
        ->assertForbidden();
});

it('prevents doctor from examining another doctors patient', function () {
    $doctor2 = User::factory()->create();
    $doctor2->assignRole('doctor');
    $doctor2->consultationTypes()->attach($this->consultationType);

    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'doctor_id' => $this->doctor->id,
        'status' => 'in_progress',
    ]);

    actingAs($doctor2)
        ->get(route('doctor.examine', $record))
        ->assertForbidden();
});

// ==================== EXAMINATION TESTS ====================

it('can save draft examination notes', function () {
    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record])
        ->set('pertinentHpiPe', 'Patient presents with fever and cough')
        ->set('diagnosis', 'Upper Respiratory Tract Infection')
        ->set('plan', 'Antibiotics and rest')
        ->call('saveDraft')
        ->assertHasNoErrors();

    $record->refresh();
    expect($record->pertinent_hpi_pe)->toBe('Patient presents with fever and cough');
    expect($record->diagnosis)->toBe('Upper Respiratory Tract Infection');
    expect($record->plan)->toBe('Antibiotics and rest');
});

it('can add prescription', function () {
    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record])
        ->call('openPrescriptionModal')
        ->assertSet('showPrescriptionModal', true)
        ->set('medicationName', 'Amoxicillin 500mg')
        ->set('dosage', '1 capsule')
        ->set('frequency', '3 times a day')
        ->set('duration', '7 days')
        ->set('quantity', 21)
        ->call('savePrescription')
        ->assertSet('showPrescriptionModal', false);

    expect(Prescription::where('medical_record_id', $record->id)->count())->toBe(1);
    $prescription = Prescription::where('medical_record_id', $record->id)->first();
    expect($prescription->medication_name)->toBe('Amoxicillin 500mg');
    expect($prescription->quantity)->toBe(21);
});

it('can edit prescription', function () {
    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    $prescription = Prescription::create([
        'medical_record_id' => $record->id,
        'prescribed_by' => $this->doctor->id,
        'medication_name' => 'Original Med',
        'dosage' => '1 tablet',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record])
        ->call('editPrescription', $prescription->id)
        ->assertSet('showPrescriptionModal', true)
        ->assertSet('medicationName', 'Original Med')
        ->set('medicationName', 'Updated Med')
        ->call('savePrescription');

    $prescription->refresh();
    expect($prescription->medication_name)->toBe('Updated Med');
});

it('can delete prescription', function () {
    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    $prescription = Prescription::create([
        'medical_record_id' => $record->id,
        'prescribed_by' => $this->doctor->id,
        'medication_name' => 'To Delete',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record])
        ->call('deletePrescription', $prescription->id);

    expect(Prescription::find($prescription->id))->toBeNull();
});

// ==================== COMPLETION TESTS ====================

it('requires diagnosis before completing examination', function () {
    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record])
        ->set('diagnosis', '') // Empty diagnosis
        ->call('openCompleteModal')
        ->assertSet('showCompleteModal', false); // Modal should not open
});

it('can complete examination for billing', function () {
    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record])
        ->set('diagnosis', 'Common Cold')
        ->set('plan', 'Rest and fluids')
        ->call('openCompleteModal')
        ->assertSet('showCompleteModal', true)
        ->set('completionAction', 'for_billing')
        ->call('completeExamination')
        ->assertRedirect(route('doctor.queue'));

    $record->refresh();
    expect($record->status)->toBe('for_billing');
    expect($record->examination_ended_at)->not->toBeNull();
});

it('can complete examination as completed', function () {
    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record])
        ->set('diagnosis', 'Routine Checkup - All Clear')
        ->call('openCompleteModal')
        ->set('completionAction', 'completed')
        ->call('completeExamination')
        ->assertRedirect(route('doctor.queue'));

    $record->refresh();
    expect($record->status)->toBe('completed');
});

// ==================== ADMISSION TESTS ====================

it('can complete examination for admission', function () {
    $patient = User::factory()->create();
    $patient->assignRole('patient');

    $record = MedicalRecord::factory()->create([
        'user_id' => $patient->id,
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record])
        ->set('diagnosis', 'Severe Pneumonia')
        ->set('plan', 'Admit for IV antibiotics')
        ->call('openCompleteModal')
        ->assertSet('admissionReason', 'Severe Pneumonia') // Pre-filled from diagnosis
        ->set('completionAction', 'for_admission')
        ->set('admissionReason', 'Severe pneumonia requiring IV antibiotics')
        ->set('admissionUrgency', 'urgent')
        ->set('admissionNotes', 'Patient has difficulty breathing')
        ->call('completeExamination')
        ->assertRedirect(route('doctor.queue'));

    $record->refresh();
    expect($record->status)->toBe('for_admission');

    // Check admission record was created
    $admission = Admission::where('medical_record_id', $record->id)->first();
    expect($admission)->not->toBeNull();
    expect($admission->user_id)->toBe($patient->id);
    expect($admission->admitted_by)->toBe($this->doctor->id);
    expect($admission->reason_for_admission)->toBe('Severe pneumonia requiring IV antibiotics');
    expect($admission->status)->toBe('active');
    expect($admission->notes)->toContain('Urgency: urgent');
});

it('validates admission fields when for_admission is selected', function () {
    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record])
        ->set('diagnosis', 'Test Diagnosis')
        ->call('openCompleteModal')
        ->set('completionAction', 'for_admission')
        ->set('admissionReason', '') // Empty reason should fail
        ->call('completeExamination')
        ->assertHasErrors(['admissionReason']);
});

it('generates unique admission numbers', function () {
    $patient1 = User::factory()->create();
    $patient1->assignRole('patient');

    $patient2 = User::factory()->create();
    $patient2->assignRole('patient');

    // Create first admission
    $record1 = MedicalRecord::factory()->create([
        'user_id' => $patient1->id,
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record1])
        ->set('diagnosis', 'Diagnosis 1')
        ->call('openCompleteModal')
        ->set('completionAction', 'for_admission')
        ->set('admissionReason', 'Reason 1')
        ->call('completeExamination');

    // Create second admission
    $record2 = MedicalRecord::factory()->create([
        'user_id' => $patient2->id,
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record2])
        ->set('diagnosis', 'Diagnosis 2')
        ->call('openCompleteModal')
        ->set('completionAction', 'for_admission')
        ->set('admissionReason', 'Reason 2')
        ->call('completeExamination');

    $admissions = Admission::all();
    expect($admissions)->toHaveCount(2);
    expect($admissions[0]->admission_number)->not->toBe($admissions[1]->admission_number);
});

// ==================== DISCOUNT RECOMMENDATION TESTS ====================

it('can set discount recommendation', function () {
    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record])
        ->set('suggestedDiscountType', 'senior')
        ->set('suggestedDiscountReason', 'Patient is 65+ years old')
        ->set('diagnosis', 'Test')
        ->call('saveDraft');

    $record->refresh();
    expect($record->suggested_discount_type)->toBe('senior');
    expect($record->suggested_discount_reason)->toBe('Patient is 65+ years old');
});

// ==================== PATIENT HISTORY TESTS ====================

it('can view patient history modal', function () {
    $record = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Examination::class, ['medicalRecord' => $record])
        ->assertSet('showHistoryModal', false)
        ->call('openHistoryModal')
        ->assertSet('showHistoryModal', true)
        ->call('closeHistoryModal')
        ->assertSet('showHistoryModal', false);
});
