<?php

use App\Livewire\Cashier\TransactionDetails;
use App\Models\BillingItem;
use App\Models\BillingTransaction;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\Service;
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

    $this->patient = User::factory()->create();
    $this->patient->assignRole('patient');

    $this->doctor = User::factory()->create();
    $this->doctor->assignRole('doctor');

    $this->consultationType = ConsultationType::factory()->create([
        'code' => 'gen',
        'name' => 'General Consultation',
    ]);

    $this->medicalRecord = MedicalRecord::factory()->create([
        'user_id' => $this->patient->id,
        'doctor_id' => $this->doctor->id,
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Test',
        'patient_last_name' => 'Patient',
        'status' => 'completed',
    ]);

    $this->transaction = BillingTransaction::create([
        'user_id' => $this->patient->id,
        'medical_record_id' => $this->medicalRecord->id,
        'transaction_number' => 'TXN-20260203-0001',
        'transaction_date' => today(),
        'subtotal' => 500.00,
        'discount_type' => 'none',
        'discount_amount' => 0,
        'total_amount' => 500.00,
        'payment_status' => 'paid',
        'amount_paid' => 500.00,
        'balance' => 0,
        'payment_method' => 'cash',
        'processed_by' => $this->cashier->id,
    ]);

    BillingItem::create([
        'billing_transaction_id' => $this->transaction->id,
        'item_type' => 'professional_fee',
        'item_description' => 'Professional Fee - General',
        'quantity' => 1,
        'unit_price' => 500.00,
        'total_price' => 500.00,
    ]);
});

// ==================== ACCESS TESTS ====================

test('cashier can view transaction details', function () {
    actingAs($this->cashier)
        ->get(route('cashier.transaction', $this->transaction))
        ->assertOk()
        ->assertSeeLivewire(TransactionDetails::class);
});

test('non-cashier cannot access transaction details', function () {
    actingAs($this->patient)
        ->get(route('cashier.transaction', $this->transaction))
        ->assertForbidden();
});

test('guest cannot access transaction details', function () {
    $this->get(route('cashier.transaction', $this->transaction))
        ->assertRedirect(route('login'));
});

// ==================== DISPLAY TESTS ====================

test('displays transaction number', function () {
    Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction])
        ->assertSee('TXN-20260203-0001');
});

test('displays patient name', function () {
    Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction])
        ->assertSee('Test')
        ->assertSee('Patient');
});

test('displays payment method', function () {
    Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction])
        ->assertSee('Cash');
});

test('displays total amount', function () {
    Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction])
        ->assertSee('500.00');
});

test('displays billing items', function () {
    Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction])
        ->assertSee('Professional Fee - General');
});

test('displays doctor name', function () {
    Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction])
        ->assertSee($this->doctor->first_name)
        ->assertSee($this->doctor->last_name);
});

test('displays consultation type', function () {
    Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction])
        ->assertSee('General Consultation');
});

// ==================== DISCOUNT DISPLAY TESTS ====================

test('displays discount information when present', function () {
    $this->transaction->update([
        'discount_type' => 'senior',
        'discount_amount' => 100.00,
        'discount_reason' => 'Senior citizen ID: 123456',
    ]);

    Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction])
        ->assertSee('Senior')
        ->assertSee('100.00');
});

// ==================== SPECIAL CHARGES DISPLAY TESTS ====================

test('displays special charges when present', function () {
    $this->transaction->update([
        'is_emergency' => true,
        'emergency_fee' => 200.00,
    ]);

    Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction])
        ->assertSee('Special Charges')
        ->assertSee('200.00');
});

// ==================== PRINT FUNCTIONALITY ====================

test('has print receipt button', function () {
    Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction])
        ->assertSee('Print Receipt');
});

test('dispatches print event when print button clicked', function () {
    Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction])
        ->call('printReceipt')
        ->assertDispatched('print-receipt');
});

// ==================== NAVIGATION TESTS ====================

test('has back to history button', function () {
    Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction])
        ->assertSee('Back to History');
});

// ==================== COMPUTED PROPERTY TESTS ====================

test('transaction computed property returns full transaction', function () {
    $component = Livewire::actingAs($this->cashier)
        ->test(TransactionDetails::class, ['transaction' => $this->transaction]);

    $transaction = $component->get('transaction');

    expect($transaction)->not->toBeNull();
    expect($transaction->id)->toBe($this->transaction->id);
    expect($transaction->billingItems)->not->toBeNull();
    expect($transaction->medicalRecord)->not->toBeNull();
});
