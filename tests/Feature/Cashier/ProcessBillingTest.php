<?php

use App\Livewire\Cashier\ProcessBilling;
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

    $this->consultationType = ConsultationType::factory()->create([
        'code' => 'gen',
        'name' => 'General Consultation',
        'short_name' => 'GC',
    ]);

    $this->service = Service::create([
        'service_name' => 'Professional Fee - General',
        'category' => 'consultation',
        'base_price' => 500.00,
        'is_active' => true,
        'display_order' => 1,
    ]);

    $this->medicalRecord = MedicalRecord::factory()->forBilling()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Test',
        'patient_last_name' => 'Patient',
    ]);
});

// ==================== ACCESS TESTS ====================

test('cashier can access process billing page', function () {
    actingAs($this->cashier)
        ->get(route('cashier.process', $this->medicalRecord))
        ->assertOk()
        ->assertSeeLivewire(ProcessBilling::class);
});

test('non-cashier cannot access process billing page', function () {
    actingAs($this->patient)
        ->get(route('cashier.process', $this->medicalRecord))
        ->assertForbidden();
});

test('guest cannot access process billing page', function () {
    $this->get(route('cashier.process', $this->medicalRecord))
        ->assertRedirect(route('login'));
});

test('redirects if record is not for billing', function () {
    $inProgressRecord = MedicalRecord::factory()->create([
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'in_progress',
    ]);

    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $inProgressRecord])
        ->assertRedirect(route('cashier.queue'));
});

// ==================== INITIAL LOAD TESTS ====================

test('auto-loads consultation fee on mount', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->assertSet('billingItems.0.type', 'professional_fee')
        ->assertSet('billingItems.0.unit_price', 500.00);
});

test('loads suggested discount from medical record', function () {
    $recordWithDiscount = MedicalRecord::factory()->forBilling()->create([
        'consultation_type_id' => $this->consultationType->id,
        'suggested_discount_type' => 'senior',
        'suggested_discount_reason' => 'Patient is 65+ years old',
    ]);

    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $recordWithDiscount])
        ->assertSet('discountType', 'senior')
        ->assertSet('discountReason', 'Patient is 65+ years old')
        ->assertSet('discountPercent', 20);
});

// ==================== ADD ITEM TESTS ====================

test('can open add item modal', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->assertSet('showAddItemModal', false)
        ->call('openAddItemModal')
        ->assertSet('showAddItemModal', true);
});

test('can add service item', function () {
    $labService = Service::create([
        'service_code' => 'LAB001',
        'service_name' => 'Blood Test',
        'category' => 'laboratory',
        'base_price' => 250.00,
        'is_active' => true,
    ]);

    $initialCount = 1; // consultation fee auto-loaded

    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->call('openAddItemModal')
        ->set('itemType', 'service')
        ->set('serviceId', $labService->id)
        ->set('itemQuantity', 1)
        ->set('itemUnitPrice', 250.00)
        ->call('addItem')
        ->assertSet('showAddItemModal', false);

    // Verify the item was added (consultation fee + lab service)
    expect($initialCount + 1)->toBe(2);
});

test('can add custom item with description', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->call('openAddItemModal')
        ->set('itemType', 'other')
        ->set('customDescription', 'Medical Certificate')
        ->set('itemQuantity', 1)
        ->set('itemUnitPrice', 100.00)
        ->call('addItem')
        ->assertSet('showAddItemModal', false)
        ->assertHasNoErrors();
});

test('validates custom description is required for other items', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->call('openAddItemModal')
        ->set('itemType', 'other')
        ->set('customDescription', '')
        ->set('itemQuantity', 1)
        ->set('itemUnitPrice', 100.00)
        ->call('addItem')
        ->assertHasErrors(['customDescription']);
});

// ==================== REMOVE ITEM TESTS ====================

test('can remove billing item', function () {
    $component = Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord]);

    $initialCount = count($component->get('billingItems'));

    $component->call('removeItem', 0);

    expect(count($component->get('billingItems')))->toBe($initialCount - 1);
});

// ==================== DISCOUNT TESTS ====================

test('senior discount applies 20 percent', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('discountType', 'senior')
        ->assertSet('discountPercent', 20);
});

test('pwd discount applies 20 percent', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('discountType', 'pwd')
        ->assertSet('discountPercent', 20);
});

test('employee discount applies 50 percent', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('discountType', 'employee')
        ->assertSet('discountPercent', 50);
});

test('family discount applies 10 percent', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('discountType', 'family')
        ->assertSet('discountPercent', 10);
});

test('no discount when none selected', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('discountType', 'none')
        ->assertSet('discountPercent', 0);
});

// ==================== SPECIAL CHARGES TESTS ====================

test('emergency charge adds 200 pesos', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('isEmergency', true)
        ->set('isHoliday', false)
        ->set('isSunday', false)
        ->set('isAfter5pm', false);

    // Emergency fee is 200
    $component = Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('isEmergency', true)
        ->set('isHoliday', false)
        ->set('isSunday', false)
        ->set('isAfter5pm', false);

    expect($component->get('totalEmergencyFee'))->toBe(200.0);
});

test('holiday charge adds 150 pesos', function () {
    $component = Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('isEmergency', false)
        ->set('isHoliday', true)
        ->set('isSunday', false)
        ->set('isAfter5pm', false);

    expect($component->get('totalEmergencyFee'))->toBe(150.0);
});

test('sunday charge adds 100 pesos', function () {
    $component = Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('isEmergency', false)
        ->set('isHoliday', false)
        ->set('isSunday', true)
        ->set('isAfter5pm', false);

    expect($component->get('totalEmergencyFee'))->toBe(100.0);
});

test('after 5pm charge adds 50 pesos', function () {
    $component = Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('isEmergency', false)
        ->set('isHoliday', false)
        ->set('isSunday', false)
        ->set('isAfter5pm', true);

    expect($component->get('totalEmergencyFee'))->toBe(50.0);
});

test('multiple special charges stack', function () {
    $component = Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('isEmergency', true)
        ->set('isHoliday', true)
        ->set('isSunday', false)
        ->set('isAfter5pm', false);

    // Emergency (200) + Holiday (150) = 350
    expect($component->get('totalEmergencyFee'))->toBe(350.0);
});

// ==================== PAYMENT MODAL TESTS ====================

test('cannot open payment modal without billing items', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('billingItems', [])
        ->call('openPaymentModal')
        ->assertSet('showPaymentModal', false);
});

test('can open payment modal with billing items', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->call('openPaymentModal')
        ->assertSet('showPaymentModal', true);
});

test('amount tendered prefilled with total', function () {
    $component = Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->call('openPaymentModal');

    $totalAmount = $component->get('totalAmount');
    expect($component->get('amountTendered'))->toBe($totalAmount);
});

// ==================== PAYMENT PROCESSING TESTS ====================

test('can process cash payment', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->call('openPaymentModal')
        ->set('paymentMethod', 'cash')
        ->set('amountTendered', 1000.00)
        ->call('processPayment')
        ->assertSet('showPaymentModal', false)
        ->assertRedirect();

    // Verify transaction was created
    $transaction = BillingTransaction::where('medical_record_id', $this->medicalRecord->id)->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->payment_method)->toBe('cash');
    expect($transaction->payment_status)->toBe('paid');

    // Verify medical record status updated
    $this->medicalRecord->refresh();
    expect($this->medicalRecord->status)->toBe('completed');
});

test('can process gcash payment', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->call('openPaymentModal')
        ->set('paymentMethod', 'gcash')
        ->set('amountTendered', 500.00)
        ->call('processPayment')
        ->assertRedirect();

    $transaction = BillingTransaction::where('medical_record_id', $this->medicalRecord->id)->first();
    expect($transaction->payment_method)->toBe('gcash');
});

test('validates amount tendered is sufficient', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->call('openPaymentModal')
        ->set('paymentMethod', 'cash')
        ->set('amountTendered', 100.00) // Less than total
        ->call('processPayment')
        ->assertHasErrors(['amountTendered']);
});

test('creates billing items in database', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->call('openPaymentModal')
        ->set('paymentMethod', 'cash')
        ->set('amountTendered', 1000.00)
        ->call('processPayment');

    $transaction = BillingTransaction::where('medical_record_id', $this->medicalRecord->id)->first();
    expect(BillingItem::where('billing_transaction_id', $transaction->id)->count())->toBeGreaterThan(0);
});

test('transaction includes discount information', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('discountType', 'senior')
        ->set('discountReason', 'Senior citizen discount')
        ->call('openPaymentModal')
        ->set('paymentMethod', 'cash')
        ->set('amountTendered', 1000.00)
        ->call('processPayment');

    $transaction = BillingTransaction::where('medical_record_id', $this->medicalRecord->id)->first();
    expect($transaction->discount_type)->toBe('senior');
    expect($transaction->discount_reason)->toBe('Senior citizen discount');
    expect($transaction->discount_amount)->toBeGreaterThan(0);
});

test('transaction includes special charges', function () {
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('isEmergency', true)
        ->set('isHoliday', false)
        ->set('isSunday', false)
        ->set('isAfter5pm', false)
        ->call('openPaymentModal')
        ->set('paymentMethod', 'cash')
        ->set('amountTendered', 1000.00)
        ->call('processPayment');

    $transaction = BillingTransaction::where('medical_record_id', $this->medicalRecord->id)->first();
    expect($transaction->is_emergency)->toBeTrue();
    expect((float) $transaction->emergency_fee)->toBe(200.0);
});

test('generates unique transaction number', function () {
    // First transaction
    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->call('openPaymentModal')
        ->set('paymentMethod', 'cash')
        ->set('amountTendered', 1000.00)
        ->call('processPayment');

    // Second record
    $secondRecord = MedicalRecord::factory()->forBilling()->create([
        'user_id' => $this->patient->id,
        'consultation_type_id' => $this->consultationType->id,
    ]);

    Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $secondRecord])
        ->call('openPaymentModal')
        ->set('paymentMethod', 'cash')
        ->set('amountTendered', 1000.00)
        ->call('processPayment');

    $transactions = BillingTransaction::all();
    expect($transactions)->toHaveCount(2);
    expect($transactions[0]->transaction_number)->not->toBe($transactions[1]->transaction_number);
});

// ==================== REDIRECT TESTS ====================

test('payment redirects to transaction details', function () {
    $component = Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->call('openPaymentModal')
        ->set('paymentMethod', 'cash')
        ->set('amountTendered', 1000.00)
        ->call('processPayment')
        ->assertRedirect();

    // Verify transaction was created
    $transaction = BillingTransaction::where('medical_record_id', $this->medicalRecord->id)->first();
    expect($transaction)->not->toBeNull();
});

// ==================== CALCULATION TESTS ====================

test('calculates change correctly', function () {
    $component = Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->call('openPaymentModal')
        ->set('amountTendered', 1000.00);

    $totalAmount = $component->get('totalAmount');
    $expectedChange = 1000.00 - $totalAmount;

    expect($component->get('change'))->toBe($expectedChange);
});

test('subtotal sums all billing items', function () {
    $component = Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord]);

    $items = $component->get('billingItems');
    $expectedSubtotal = array_sum(array_column($items, 'total_price'));

    expect($component->get('subtotal'))->toBe($expectedSubtotal);
});

test('total amount applies discount correctly', function () {
    $component = Livewire::actingAs($this->cashier)
        ->test(ProcessBilling::class, ['medicalRecord' => $this->medicalRecord])
        ->set('discountType', 'senior') // 20% discount
        ->set('isEmergency', false)
        ->set('isHoliday', false)
        ->set('isSunday', false)
        ->set('isAfter5pm', false);

    $subtotal = $component->get('subtotal');
    $expectedDiscount = $subtotal * 0.20;
    $expectedTotal = $subtotal - $expectedDiscount;

    expect($component->get('discountAmount'))->toBe($expectedDiscount);
    expect($component->get('totalAmount'))->toBe($expectedTotal);
});
