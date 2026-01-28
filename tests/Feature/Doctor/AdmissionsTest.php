<?php

use App\Livewire\Doctor\Admissions;
use App\Models\Admission;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\PersonalInformation;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::findOrCreate('doctor', 'web');
    Role::findOrCreate('patient', 'web');

    $this->doctor = User::factory()->create();
    $this->doctor->assignRole('doctor');

    PersonalInformation::factory()->create([
        'user_id' => $this->doctor->id,
        'first_name' => 'Test',
        'last_name' => 'Doctor',
    ]);

    $this->consultationType = ConsultationType::factory()->create([
        'code' => 'gen',
        'name' => 'General Medicine',
        'short_name' => 'G',
    ]);

    $this->doctor->consultationTypes()->attach($this->consultationType);

    $this->patient = User::factory()->create();
    $this->patient->assignRole('patient');
});

// ==================== ACCESS TESTS ====================

it('renders the admissions page for doctors', function () {
    actingAs($this->doctor)
        ->get(route('doctor.admissions'))
        ->assertSuccessful()
        ->assertSee('Admissions');
});

it('denies access to non-doctors', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('doctor.admissions'))
        ->assertForbidden();
});

// ==================== LISTING TESTS ====================

it('lists active admissions by default', function () {
    $record = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'for_admission',
    ]);

    $admission = Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record->id,
        'admitted_by' => $this->doctor->id,
        'admission_number' => 'ADM-2026-00001',
        'admission_date' => now(),
        'reason_for_admission' => 'Test reason',
        'status' => 'active',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Admissions::class)
        ->assertSet('status', 'active')
        ->assertSee('2026-00001'); // View uses Str::after to strip ADM- prefix
});

it('can switch between active and discharged status', function () {
    Livewire::actingAs($this->doctor)
        ->test(Admissions::class)
        ->assertSet('status', 'active')
        ->call('setStatus', 'discharged')
        ->assertSet('status', 'discharged')
        ->call('setStatus', 'active')
        ->assertSet('status', 'active');
});

it('only shows admissions created by the logged-in doctor', function () {
    $otherDoctor = User::factory()->create();
    $otherDoctor->assignRole('doctor');

    $record1 = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
    ]);

    $record2 = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
    ]);

    Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record1->id,
        'admitted_by' => $this->doctor->id,
        'admission_number' => 'ADM-2026-00001',
        'admission_date' => now(),
        'reason_for_admission' => 'My admission',
        'status' => 'active',
    ]);

    Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record2->id,
        'admitted_by' => $otherDoctor->id,
        'admission_number' => 'ADM-2026-00002',
        'admission_date' => now(),
        'reason_for_admission' => 'Other doctors admission',
        'status' => 'active',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Admissions::class)
        ->assertSee('2026-00001') // View uses Str::after to strip ADM- prefix
        ->assertDontSee('2026-00002');
});

// ==================== SELECTION TESTS ====================

it('can select and deselect admission', function () {
    $record = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
    ]);

    $admission = Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record->id,
        'admitted_by' => $this->doctor->id,
        'admission_number' => 'ADM-2026-00001',
        'admission_date' => now(),
        'reason_for_admission' => 'Test',
        'status' => 'active',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Admissions::class)
        ->assertSet('selectedAdmissionId', null)
        ->call('selectAdmission', $admission->id)
        ->assertSet('selectedAdmissionId', $admission->id)
        ->call('selectAdmission', $admission->id) // Click again to deselect
        ->assertSet('selectedAdmissionId', null);
});

// ==================== EDIT TESTS ====================

it('can open edit modal with admission data', function () {
    $record = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
    ]);

    $admission = Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record->id,
        'admitted_by' => $this->doctor->id,
        'admission_number' => 'ADM-2026-00001',
        'admission_date' => now(),
        'reason_for_admission' => 'Test',
        'room_number' => '101',
        'bed_number' => 'A',
        'notes' => 'Some notes',
        'status' => 'active',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Admissions::class)
        ->call('selectAdmission', $admission->id)
        ->call('openEditModal')
        ->assertSet('showEditModal', true)
        ->assertSet('roomNumber', '101')
        ->assertSet('bedNumber', 'A')
        ->assertSet('notes', 'Some notes');
});

it('can update admission details', function () {
    $record = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
    ]);

    $admission = Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record->id,
        'admitted_by' => $this->doctor->id,
        'admission_number' => 'ADM-2026-00001',
        'admission_date' => now(),
        'reason_for_admission' => 'Test',
        'status' => 'active',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Admissions::class)
        ->call('selectAdmission', $admission->id)
        ->call('openEditModal')
        ->set('roomNumber', '202')
        ->set('bedNumber', 'B')
        ->set('notes', 'Updated notes')
        ->call('saveAdmission')
        ->assertSet('showEditModal', false);

    $admission->refresh();
    expect($admission->room_number)->toBe('202');
    expect($admission->bed_number)->toBe('B');
    expect($admission->notes)->toBe('Updated notes');
});

// ==================== DISCHARGE TESTS ====================

it('can open discharge modal', function () {
    $record = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
    ]);

    $admission = Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record->id,
        'admitted_by' => $this->doctor->id,
        'admission_number' => 'ADM-2026-00001',
        'admission_date' => now(),
        'reason_for_admission' => 'Test',
        'status' => 'active',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Admissions::class)
        ->call('selectAdmission', $admission->id)
        ->call('openDischargeModal')
        ->assertSet('showDischargeModal', true);
});

it('can discharge a patient', function () {
    $record = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'for_admission',
    ]);

    $admission = Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record->id,
        'admitted_by' => $this->doctor->id,
        'admission_number' => 'ADM-2026-00001',
        'admission_date' => now()->subDays(3),
        'reason_for_admission' => 'Test',
        'status' => 'active',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Admissions::class)
        ->call('selectAdmission', $admission->id)
        ->call('openDischargeModal')
        ->set('dischargeSummary', 'Patient recovered well. Follow up in 2 weeks.')
        ->call('dischargePatient')
        ->assertSet('showDischargeModal', false)
        ->assertSet('selectedAdmissionId', null);

    $admission->refresh();
    expect($admission->status)->toBe('discharged');
    expect($admission->discharge_date)->not->toBeNull();
    expect($admission->discharge_summary)->toBe('Patient recovered well. Follow up in 2 weeks.');

    $record->refresh();
    expect($record->status)->toBe('completed');
});

it('validates discharge summary is required', function () {
    $record = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
    ]);

    $admission = Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record->id,
        'admitted_by' => $this->doctor->id,
        'admission_number' => 'ADM-2026-00001',
        'admission_date' => now(),
        'reason_for_admission' => 'Test',
        'status' => 'active',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Admissions::class)
        ->call('selectAdmission', $admission->id)
        ->call('openDischargeModal')
        ->set('dischargeSummary', '')
        ->call('dischargePatient')
        ->assertHasErrors(['dischargeSummary']);
});

it('cannot discharge already discharged patient', function () {
    $record = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
    ]);

    $admission = Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record->id,
        'admitted_by' => $this->doctor->id,
        'admission_number' => 'ADM-2026-00001',
        'admission_date' => now()->subDays(3),
        'discharge_date' => now(),
        'reason_for_admission' => 'Test',
        'discharge_summary' => 'Already discharged',
        'status' => 'discharged',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(Admissions::class)
        ->call('setStatus', 'discharged')
        ->call('selectAdmission', $admission->id)
        ->call('openDischargeModal')
        ->assertSet('showDischargeModal', false); // Modal should not open
});

// ==================== STATUS COUNT TESTS ====================

it('shows correct status counts', function () {
    $record1 = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
    ]);

    $record2 = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
    ]);

    $record3 = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
    ]);

    Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record1->id,
        'admitted_by' => $this->doctor->id,
        'admission_number' => 'ADM-2026-00001',
        'admission_date' => now(),
        'reason_for_admission' => 'Test 1',
        'status' => 'active',
    ]);

    Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record2->id,
        'admitted_by' => $this->doctor->id,
        'admission_number' => 'ADM-2026-00002',
        'admission_date' => now(),
        'reason_for_admission' => 'Test 2',
        'status' => 'active',
    ]);

    Admission::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $record3->id,
        'admitted_by' => $this->doctor->id,
        'admission_number' => 'ADM-2026-00003',
        'admission_date' => now()->subDays(5),
        'discharge_date' => now(),
        'reason_for_admission' => 'Test 3',
        'discharge_summary' => 'Discharged',
        'status' => 'discharged',
    ]);

    $component = Livewire::actingAs($this->doctor)
        ->test(Admissions::class);

    expect($component->get('statusCounts'))->toBe([
        'active' => 2,
        'discharged' => 1,
    ]);
});
