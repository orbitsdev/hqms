<?php

use App\Livewire\Nurse\MedicalRecords;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\PersonalInformation;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::findOrCreate('nurse', 'web');
    Role::findOrCreate('doctor', 'web');

    $this->nurse = User::factory()->create();
    $this->nurse->assignRole('nurse');

    PersonalInformation::factory()->create([
        'user_id' => $this->nurse->id,
        'first_name' => 'Test',
        'last_name' => 'Nurse',
    ]);

    $this->consultationType = ConsultationType::factory()->create([
        'code' => 'ob',
        'name' => 'Obstetrics',
        'short_name' => 'O',
    ]);

    $this->patient = User::factory()->create();

    $this->medicalRecord = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
        'nurse_id' => $this->nurse->id,
        'patient_first_name' => 'Maria',
        'patient_last_name' => 'Santos',
        'patient_date_of_birth' => '1990-05-15',
        'patient_gender' => 'female',
        'visit_date' => now(),
        'status' => 'in_progress',
        'vital_signs_recorded_at' => null,
        'temperature' => null,
        'blood_pressure' => null,
        'cardiac_rate' => null,
        'respiratory_rate' => null,
    ]);
});

describe('Medical Records Page', function () {
    it('renders the medical records page', function () {
        actingAs($this->nurse)
            ->get(route('nurse.medical-records'))
            ->assertSuccessful()
            ->assertSee('Medical Records');
    });

    it('displays medical records', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->assertSee('Maria')
            ->assertSee('Santos');
    });

    it('shows record number in the list', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->assertSee($this->medicalRecord->record_number);
    });
});

describe('Search and Filters', function () {
    it('can search records by patient name', function () {
        MedicalRecord::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Juan',
            'patient_last_name' => 'Cruz',
            'visit_date' => now(),
        ]);

        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->set('search', 'Maria')
            ->assertSee('Maria Santos')
            ->assertDontSee('Juan Cruz');
    });

    it('can search records by record number', function () {
        $record2 = MedicalRecord::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Juan',
            'patient_last_name' => 'Cruz',
            'visit_date' => now(),
        ]);

        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->set('search', $this->medicalRecord->record_number)
            ->assertSee($this->medicalRecord->record_number)
            ->assertDontSee($record2->record_number);
    });

    it('can filter by consultation type', function () {
        $pedType = ConsultationType::factory()->create([
            'code' => 'ped',
            'name' => 'Pediatrics',
            'short_name' => 'P',
        ]);

        MedicalRecord::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $pedType->id,
            'patient_first_name' => 'Child',
            'patient_last_name' => 'Patient',
            'visit_date' => now(),
        ]);

        $component = Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->set('dateFrom', now()->subDays(30)->format('Y-m-d'))
            ->set('dateTo', now()->format('Y-m-d'))
            ->set('consultationTypeFilter', $this->consultationType->id);

        // Check the records are filtered correctly
        $records = $component->viewData('records');
        expect($records->count())->toBe(1)
            ->and($records->first()->patient_first_name)->toBe('Maria');
    });

    it('can filter by status', function () {
        // Update the base record to ensure it's in_progress
        $this->medicalRecord->update(['status' => 'in_progress']);

        MedicalRecord::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Completed',
            'patient_last_name' => 'Patient',
            'visit_date' => now()->subDays(5),
            'status' => 'completed',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->set('dateFrom', now()->subDays(30)->format('Y-m-d'))
            ->set('dateTo', now()->format('Y-m-d'))
            ->set('statusFilter', 'in_progress')
            ->assertSee('Maria')
            ->assertDontSee('Completed Patient');
    });

    it('can filter by date range', function () {
        MedicalRecord::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Old',
            'patient_last_name' => 'Record',
            'visit_date' => now()->subDays(60),
        ]);

        $component = Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->set('dateFrom', now()->subDays(7)->format('Y-m-d'))
            ->set('dateTo', now()->format('Y-m-d'));

        // Check the records are filtered correctly - only Maria's record should be in range
        $records = $component->viewData('records');
        expect($records->count())->toBe(1)
            ->and($records->first()->patient_first_name)->toBe('Maria');
    });

    it('can clear filters', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->set('search', 'test')
            ->set('statusFilter', 'completed')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('statusFilter', '');
    });

    it('can toggle filters panel', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->assertSet('showFilters', false)
            ->call('toggleFilters')
            ->assertSet('showFilters', true)
            ->call('toggleFilters')
            ->assertSet('showFilters', false);
    });
});

describe('View Record', function () {
    it('can open view modal', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('viewRecord', $this->medicalRecord->id)
            ->assertSet('showViewModal', true)
            ->assertSet('viewRecordId', $this->medicalRecord->id);
    });

    it('can close view modal', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('viewRecord', $this->medicalRecord->id)
            ->call('closeViewModal')
            ->assertSet('showViewModal', false)
            ->assertSet('viewRecordId', null);
    });

    it('can switch view tabs', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('viewRecord', $this->medicalRecord->id)
            ->assertSet('viewTab', 'patient')
            ->call('setViewTab', 'vitals')
            ->assertSet('viewTab', 'vitals')
            ->call('setViewTab', 'diagnosis')
            ->assertSet('viewTab', 'diagnosis');
    });
});

describe('Edit Record', function () {
    it('can open edit modal with populated data', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->assertSet('showEditModal', true)
            ->assertSet('editRecordId', $this->medicalRecord->id)
            ->assertSet('patientFirstName', 'Maria')
            ->assertSet('patientLastName', 'Santos')
            ->assertSet('patientGender', 'female');
    });

    it('can close edit modal', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->call('closeEditModal')
            ->assertSet('showEditModal', false)
            ->assertSet('editRecordId', null);
    });

    it('can navigate through edit steps', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->assertSet('editStep', 'patient')
            ->call('nextStep')
            ->assertSet('editStep', 'address')
            ->call('nextStep')
            ->assertSet('editStep', 'companion')
            ->call('previousStep')
            ->assertSet('editStep', 'address');
    });

    it('can jump to specific edit step', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->call('setEditStep', 'vitals')
            ->assertSet('editStep', 'vitals');
    });

    it('can update patient information', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->set('patientFirstName', 'Ana')
            ->set('patientLastName', 'Garcia')
            ->set('patientContactNumber', '09171234567')
            ->call('saveRecord');

        $this->medicalRecord->refresh();

        expect($this->medicalRecord->patient_first_name)->toBe('Ana')
            ->and($this->medicalRecord->patient_last_name)->toBe('Garcia')
            ->and($this->medicalRecord->patient_contact_number)->toBe('09171234567');
    });

    it('can update address information', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->set('patientProvince', 'Laguna')
            ->set('patientMunicipality', 'Santa Rosa')
            ->set('patientBarangay', 'Tagapo')
            ->call('saveRecord');

        $this->medicalRecord->refresh();

        expect($this->medicalRecord->patient_province)->toBe('Laguna')
            ->and($this->medicalRecord->patient_municipality)->toBe('Santa Rosa')
            ->and($this->medicalRecord->patient_barangay)->toBe('Tagapo');
    });

    it('can update companion information', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->set('companionName', 'Juan Santos')
            ->set('companionContact', '09181234567')
            ->set('companionRelationship', 'Spouse')
            ->call('saveRecord');

        $this->medicalRecord->refresh();

        expect($this->medicalRecord->companion_name)->toBe('Juan Santos')
            ->and($this->medicalRecord->companion_contact)->toBe('09181234567')
            ->and($this->medicalRecord->companion_relationship)->toBe('Spouse');
    });

    it('can update medical background', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->set('patientBloodType', 'O+')
            ->set('patientAllergies', 'Penicillin, Shellfish')
            ->set('patientChronicConditions', 'Hypertension')
            ->call('saveRecord');

        $this->medicalRecord->refresh();

        expect($this->medicalRecord->patient_blood_type)->toBe('O+')
            ->and($this->medicalRecord->patient_allergies)->toBe('Penicillin, Shellfish')
            ->and($this->medicalRecord->patient_chronic_conditions)->toBe('Hypertension');
    });

    it('can update vital signs', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->set('temperature', '37.5')
            ->set('bloodPressure', '120/80')
            ->set('cardiacRate', 72)
            ->set('respiratoryRate', 16)
            ->set('weight', '65.5')
            ->set('height', '160')
            ->call('saveRecord');

        $this->medicalRecord->refresh();

        expect((float) $this->medicalRecord->temperature)->toBe(37.5)
            ->and($this->medicalRecord->blood_pressure)->toBe('120/80')
            ->and($this->medicalRecord->cardiac_rate)->toBe(72)
            ->and($this->medicalRecord->respiratory_rate)->toBe(16);
    });

    it('sets vital_signs_recorded_at when adding vitals', function () {
        expect($this->medicalRecord->vital_signs_recorded_at)->toBeNull();

        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->set('temperature', '36.8')
            ->call('saveRecord');

        $this->medicalRecord->refresh();

        expect($this->medicalRecord->vital_signs_recorded_at)->not->toBeNull();
    });

    it('validates required fields', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->set('patientFirstName', '')
            ->set('patientLastName', '')
            ->call('saveRecord')
            ->assertHasErrors(['patientFirstName', 'patientLastName']);
    });

    it('validates blood pressure format', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->set('bloodPressure', 'invalid')
            ->call('saveRecord')
            ->assertHasErrors(['bloodPressure']);
    });

    it('validates temperature range', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->set('temperature', '50')
            ->call('saveRecord')
            ->assertHasErrors(['temperature']);
    });

    it('allows saving with empty date of birth', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('editRecord', $this->medicalRecord->id)
            ->set('patientDateOfBirth', '')
            ->call('saveRecord')
            ->assertHasNoErrors(['patientDateOfBirth']);

        $this->medicalRecord->refresh();

        expect($this->medicalRecord->patient_date_of_birth)->toBeNull();
    });
});

describe('Sorting', function () {
    it('can sort by record number', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('sortBy', 'record_number')
            ->assertSet('sortField', 'record_number')
            ->assertSet('sortDirection', 'asc');
    });

    it('toggles sort direction when clicking same field', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->call('sortBy', 'visit_date')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'visit_date')
            ->assertSet('sortDirection', 'desc');
    });

    it('resets to asc when sorting different field', function () {
        Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class)
            ->set('sortField', 'visit_date')
            ->set('sortDirection', 'desc')
            ->call('sortBy', 'patient_last_name')
            ->assertSet('sortField', 'patient_last_name')
            ->assertSet('sortDirection', 'asc');
    });
});

describe('Stats', function () {
    it('shows stats for today and for_billing', function () {
        // Create one more record for billing
        MedicalRecord::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'visit_date' => now(),
            'status' => 'for_billing',
        ]);

        $component = Livewire::actingAs($this->nurse)
            ->test(MedicalRecords::class);

        // Check that the stats are computed
        $stats = $component->viewData('stats');
        expect($stats['today'])->toBeGreaterThanOrEqual(1)
            ->and($stats['for_billing'])->toBeGreaterThanOrEqual(1);
    });
});

describe('Authorization', function () {
    it('requires authentication', function () {
        $this->get(route('nurse.medical-records'))
            ->assertRedirect(route('login'));
    });

    it('requires nurse role', function () {
        $patient = User::factory()->create();
        Role::findOrCreate('patient', 'web');
        $patient->assignRole('patient');

        actingAs($patient)
            ->get(route('nurse.medical-records'))
            ->assertForbidden();
    });
});
