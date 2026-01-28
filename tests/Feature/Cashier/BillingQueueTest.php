<?php

use App\Livewire\Cashier\BillingQueue;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::findOrCreate('cashier', 'web');
    Role::findOrCreate('patient', 'web');
    Role::findOrCreate('doctor', 'web');

    $this->cashier = User::factory()->create();
    $this->cashier->assignRole('cashier');

    $this->consultationType = ConsultationType::factory()->create([
        'code' => 'gen',
        'name' => 'General Consultation',
        'short_name' => 'GC',
    ]);
});

// ==================== ACCESS TESTS ====================

test('cashier can access billing queue', function () {
    actingAs($this->cashier)
        ->get(route('cashier.queue'))
        ->assertOk()
        ->assertSeeLivewire(BillingQueue::class);
});

test('non-cashier cannot access billing queue', function () {
    $patient = User::factory()->create();
    $patient->assignRole('patient');

    actingAs($patient)
        ->get(route('cashier.queue'))
        ->assertForbidden();
});

test('guest cannot access billing queue', function () {
    $this->get(route('cashier.queue'))
        ->assertRedirect(route('login'));
});

// ==================== QUEUE DISPLAY TESTS ====================

test('billing queue shows records with for_billing status', function () {
    $forBillingRecord = MedicalRecord::factory()->forBilling()->create([
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'John',
        'patient_last_name' => 'Doe',
    ]);

    $inProgressRecord = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Jane',
        'patient_last_name' => 'Smith',
        'status' => 'in_progress',
    ]);

    Livewire::actingAs($this->cashier)
        ->test(BillingQueue::class)
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

test('billing queue shows correct queue count', function () {
    MedicalRecord::factory()->forBilling()->count(3)->create([
        'consultation_type_id' => $this->consultationType->id,
    ]);

    MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
    ]);

    Livewire::actingAs($this->cashier)
        ->test(BillingQueue::class)
        ->assertSee('3'); // Queue count
});

// ==================== SEARCH TESTS ====================

test('can search by patient first name', function () {
    MedicalRecord::factory()->forBilling()->create([
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Maria',
        'patient_last_name' => 'Santos',
    ]);

    MedicalRecord::factory()->forBilling()->create([
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Pedro',
        'patient_last_name' => 'Cruz',
    ]);

    Livewire::actingAs($this->cashier)
        ->test(BillingQueue::class)
        ->set('search', 'Maria')
        ->assertSee('Maria Santos')
        ->assertDontSee('Pedro Cruz');
});

test('can search by patient last name', function () {
    MedicalRecord::factory()->forBilling()->create([
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Maria',
        'patient_last_name' => 'Santos',
    ]);

    MedicalRecord::factory()->forBilling()->create([
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Pedro',
        'patient_last_name' => 'Cruz',
    ]);

    Livewire::actingAs($this->cashier)
        ->test(BillingQueue::class)
        ->set('search', 'Cruz')
        ->assertDontSee('Maria Santos')
        ->assertSee('Pedro Cruz');
});

test('can search by record number', function () {
    $record = MedicalRecord::factory()->forBilling()->create([
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Target',
        'patient_last_name' => 'Patient',
    ]);

    MedicalRecord::factory()->forBilling()->create([
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Other',
        'patient_last_name' => 'Patient',
    ]);

    Livewire::actingAs($this->cashier)
        ->test(BillingQueue::class)
        ->set('search', $record->record_number)
        ->assertSee('Target Patient')
        ->assertDontSee('Other Patient');
});

// ==================== FILTER TESTS ====================

test('can filter by consultation type', function () {
    $pediatrics = ConsultationType::factory()->create([
        'code' => 'ped',
        'name' => 'Pediatrics',
    ]);

    MedicalRecord::factory()->forBilling()->create([
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'General',
        'patient_last_name' => 'Patient',
    ]);

    MedicalRecord::factory()->forBilling()->create([
        'consultation_type_id' => $pediatrics->id,
        'patient_first_name' => 'Pedia',
        'patient_last_name' => 'Patient',
    ]);

    Livewire::actingAs($this->cashier)
        ->test(BillingQueue::class)
        ->set('consultationFilter', $pediatrics->id)
        ->assertDontSee('General Patient')
        ->assertSee('Pedia Patient');
});

test('shows all records when no filter applied', function () {
    $pediatrics = ConsultationType::factory()->create([
        'code' => 'ped',
        'name' => 'Pediatrics',
    ]);

    MedicalRecord::factory()->forBilling()->create([
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'General',
        'patient_last_name' => 'Patient',
    ]);

    MedicalRecord::factory()->forBilling()->create([
        'consultation_type_id' => $pediatrics->id,
        'patient_first_name' => 'Pedia',
        'patient_last_name' => 'Patient',
    ]);

    Livewire::actingAs($this->cashier)
        ->test(BillingQueue::class)
        ->assertSee('General Patient')
        ->assertSee('Pedia Patient');
});

// ==================== PAGINATION TESTS ====================

test('search resets pagination', function () {
    Livewire::actingAs($this->cashier)
        ->test(BillingQueue::class)
        ->set('paginators.page', 2)
        ->set('search', 'test')
        ->assertSet('paginators.page', 1);
});

test('filter resets pagination', function () {
    Livewire::actingAs($this->cashier)
        ->test(BillingQueue::class)
        ->set('paginators.page', 2)
        ->set('consultationFilter', $this->consultationType->id)
        ->assertSet('paginators.page', 1);
});
