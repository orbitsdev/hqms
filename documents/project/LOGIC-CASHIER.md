# Cashier Module Logic

## Overview

The Cashier Desk processes payments for completed medical visits. Cashiers:
- View billing queue (records marked "for_billing")
- Create billing transactions with itemized charges
- Apply discounts (senior, PWD, employee, family)
- Process payments (cash, GCash, card, bank transfer)
- Generate receipts

---

## Tables Used

| Table | Purpose in Cashier Module |
|-------|---------------------------|
| `medical_records` | Records ready for billing (`status = 'for_billing'`) |
| `billing_transactions` | Payment records per visit |
| `billing_items` | Line items (fees, drugs, services) |
| `services` | Billable services catalog |
| `hospital_drugs` | Drug inventory with pricing |
| `prescriptions` | Doctor's prescriptions (hospital drugs) |

---

## 1. Billing Queue

### 1.1 Queue Display

**Component:** `Cashier\BillingQueue`

Records appear in billing queue when doctor marks them "for_billing":

```php
$records = MedicalRecord::query()
    ->where('status', 'for_billing')
    ->with(['consultationType', 'doctor', 'prescriptions' => function ($q) {
        $q->where('is_hospital_drug', true);
    }])
    ->orderBy('examination_ended_at')  // First finished = first served
    ->paginate(15);
```

### 1.2 Queue Entry Information

Each queue entry shows:
- Patient name: `patient_first_name`, `patient_last_name`
- Record number: `record_number` (e.g., MR-2026-00001)
- Consultation type: via `consultation_type_id`
- Doctor: via `doctor_id`
- Hospital drugs count (for quick reference)

---

## 2. Processing Billing

### 2.1 Billing Transaction

**Migration:** `2026_01_19_090008_create_billing_transactions_table.php`

**Component:** `Cashier\ProcessBilling`

```php
// billing_transactions table
BillingTransaction::create([
    'user_id' => $medicalRecord->user_id,  // Account owner
    'medical_record_id' => $medicalRecordId,
    'transaction_number' => 'TXN-20260130-0001',
    'transaction_date' => today(),

    // Emergency/After Hours
    'is_emergency' => false,
    'is_holiday' => false,
    'is_sunday' => false,
    'is_after_5pm' => true,
    'emergency_fee' => 50.00,  // ₱50 after 5pm fee

    // Amounts
    'subtotal' => 1500.00,
    'discount_type' => 'senior',
    'discount_amount' => 310.00,  // 20% of (1500 + 50)
    'discount_reason' => 'Senior Citizen ID: 12345',
    'total_amount' => 1240.00,

    // Payment
    'payment_status' => 'paid',
    'amount_paid' => 1500.00,
    'balance' => 0,
    'payment_method' => 'cash',

    // Timing
    'received_in_billing_at' => now(),
    'ended_in_billing_at' => now(),

    // Staff
    'processed_by' => Auth::id(),
]);
```

### 2.2 Transaction Number Generation

```php
$transactionNumber = 'TXN-' . date('Ymd') . '-' . str_pad(
    BillingTransaction::whereDate('created_at', today())->count() + 1,
    4,
    '0',
    STR_PAD_LEFT
);
// Example: TXN-20260130-0001
```

### 2.3 Emergency/After Hours Fees

```php
$fee = 0;
if ($this->isEmergency) $fee += 200;  // Emergency case
if ($this->isHoliday) $fee += 150;    // Holiday surcharge
if ($this->isSunday) $fee += 100;     // Sunday surcharge
if ($this->isAfter5pm) $fee += 50;    // After 5pm surcharge
```

**Auto-detection:**
```php
$now = now();
$this->isAfter5pm = $now->hour >= 17;
$this->isSunday = $now->isSunday();
```

---

## 3. Billing Items

### 3.1 Item Types

**Migration:** `2026_01_19_090009_create_billing_items_table.php`

```php
'item_type' => enum [
    'professional_fee',  // Doctor consultation fee
    'service',          // Hospital services
    'drug',             // Hospital drugs
    'procedure',        // Medical procedures
    'other',            // Custom charges
]
```

### 3.2 Auto-loaded Items

When processing billing, items are auto-populated:

```php
protected function loadInitialItems(): void
{
    $record = $this->medicalRecord;

    // 1. Professional Fee (based on consultation type)
    $feeServiceName = match ($consultationType->code ?? '') {
        'ob' => 'Professional Fee - OB',
        'pedia' => 'Professional Fee - Pediatrics',
        default => 'Professional Fee - General',
    };

    $consultationService = Service::where('service_name', $feeServiceName)
        ->where('is_active', true)
        ->first();

    if ($consultationService) {
        $this->billingItems[] = [
            'type' => 'professional_fee',
            'description' => $consultationService->service_name,
            'service_id' => $consultationService->id,
            'drug_id' => null,
            'quantity' => 1,
            'unit_price' => $consultationService->base_price,
            'total_price' => $consultationService->base_price,
        ];
    }

    // 2. Hospital Drugs (from prescriptions)
    $prescriptions = $record->prescriptions()
        ->where('is_hospital_drug', true)
        ->with('hospitalDrug')
        ->get();

    foreach ($prescriptions as $prescription) {
        if ($prescription->hospitalDrug) {
            $qty = $prescription->quantity ?? 1;
            $unitPrice = $prescription->hospitalDrug->unit_price;

            $this->billingItems[] = [
                'type' => 'drug',
                'description' => $prescription->hospitalDrug->drug_name,
                'service_id' => null,
                'drug_id' => $prescription->hospital_drug_id,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $qty * $unitPrice,
            ];
        }
    }
}
```

### 3.3 Creating Billing Items

```php
// billing_items table
foreach ($this->billingItems as $item) {
    BillingItem::create([
        'billing_transaction_id' => $transaction->id,
        'item_type' => $item['type'],
        'item_description' => $item['description'],
        'service_id' => $item['service_id'],
        'hospital_drug_id' => $item['drug_id'],
        'quantity' => $item['quantity'],
        'unit_price' => $item['unit_price'],
        'total_price' => $item['total_price'],
    ]);
}
```

### 3.4 Manual Item Addition

Cashier can add additional items:

```php
public function addItem(): void
{
    if ($this->itemType === 'service' && $this->serviceId) {
        $service = Service::find($this->serviceId);
        $description = $service->service_name;
    } elseif ($this->itemType === 'drug' && $this->drugId) {
        $drug = HospitalDrug::find($this->drugId);
        $description = $drug->drug_name;
    } else {
        $description = $this->customDescription;  // Other/procedure
    }

    $this->billingItems[] = [
        'type' => $this->itemType,
        'description' => $description,
        'service_id' => $serviceId,
        'drug_id' => $drugId,
        'quantity' => $this->itemQuantity,
        'unit_price' => $this->itemUnitPrice,
        'total_price' => $this->itemQuantity * $this->itemUnitPrice,
    ];
}
```

---

## 4. Discount System

### 4.1 Discount Types

```php
'discount_type' => enum [
    'none',      // No discount
    'family',    // Family of employee (10%)
    'senior',    // Senior citizen (20%)
    'pwd',       // Person with disability (20%)
    'employee',  // Hospital employee (50%)
    'other',     // Custom discount
]
```

### 4.2 Discount Percentages

```php
protected function setDiscountPercent(): void
{
    $this->discountPercent = match ($this->discountType) {
        'senior', 'pwd' => 20,    // Mandated by Philippine law
        'employee' => 50,          // Hospital policy
        'family' => 10,            // Hospital policy
        default => 0,
    };
}
```

### 4.3 Doctor's Discount Suggestion

Doctor can suggest discount during examination:

```php
// In medical_records table
$record->suggested_discount_type = 'senior';
$record->suggested_discount_reason = 'Presented Senior Citizen ID';

// Cashier auto-loads suggestion
$this->discountType = $medicalRecord->suggested_discount_type ?? 'none';
$this->discountReason = $medicalRecord->suggested_discount_reason ?? '';
```

### 4.4 Discount Calculation

```php
// Discount applies to subtotal + emergency fees
$discountAmount = ($this->subtotal + $this->totalEmergencyFee)
                  * ($this->discountPercent / 100);

$totalAmount = $this->subtotal + $this->totalEmergencyFee - $discountAmount;
```

---

## 5. Payment Processing

### 5.1 Payment Methods

```php
'payment_method' => enum [
    'cash',
    'gcash',
    'card',
    'bank_transfer',
    'philhealth',
]
```

### 5.2 Process Payment

```php
public function processPayment(): void
{
    $this->validate([
        'paymentMethod' => 'required|in:cash,gcash,card,bank_transfer,philhealth',
        'amountTendered' => 'required|numeric|min:' . $this->totalAmount,
    ]);

    DB::transaction(function () {
        // 1. Create billing transaction
        $transaction = BillingTransaction::create([...]);

        // 2. Create billing items
        foreach ($this->billingItems as $item) {
            BillingItem::create([...]);
        }

        // 3. Update medical record status
        $this->medicalRecord->update(['status' => 'completed']);

        // 4. Notify patient
        $patientUser->notify(new GenericNotification([
            'type' => 'billing.completed',
            'title' => 'Payment Completed',
            'message' => "Your payment of ₱{$total} has been processed.",
        ]));
    });
}
```

### 5.3 Change Calculation

```php
public function change(): float
{
    return max(0, $this->amountTendered - $this->totalAmount);
}
```

---

## 6. Payment History

### 6.1 Transaction List

**Component:** `Cashier\PaymentHistory`

```php
$transactions = BillingTransaction::query()
    ->with(['medicalRecord', 'user', 'processedBy'])
    ->when($this->search, fn ($q) => $q
        ->where('transaction_number', 'like', "%{$this->search}%"))
    ->when($this->dateFilter, fn ($q) => $q
        ->whereDate('transaction_date', $this->dateFilter))
    ->orderByDesc('created_at')
    ->paginate(15);
```

### 6.2 Daily Summary

```php
$todayStats = [
    'total_transactions' => BillingTransaction::whereDate('created_at', today())
        ->where('payment_status', 'paid')
        ->count(),

    'total_revenue' => BillingTransaction::whereDate('created_at', today())
        ->where('payment_status', 'paid')
        ->sum('total_amount'),

    'total_discounts' => BillingTransaction::whereDate('created_at', today())
        ->sum('discount_amount'),
];
```

---

## 7. Receipt Generation

### 7.1 Receipt Data

```php
$receipt = [
    'transaction_number' => $transaction->transaction_number,
    'date' => $transaction->transaction_date->format('M d, Y'),
    'time' => $transaction->ended_in_billing_at->format('h:i A'),

    'patient_name' => $medicalRecord->patient_full_name,
    'record_number' => $medicalRecord->record_number,

    'items' => $transaction->billingItems->map(fn ($item) => [
        'description' => $item->item_description,
        'quantity' => $item->quantity,
        'unit_price' => $item->unit_price,
        'total' => $item->total_price,
    ]),

    'subtotal' => $transaction->subtotal,
    'emergency_fee' => $transaction->emergency_fee,
    'discount_type' => $transaction->discount_type,
    'discount_amount' => $transaction->discount_amount,
    'total_amount' => $transaction->total_amount,
    'amount_paid' => $transaction->amount_paid,
    'change' => $transaction->amount_paid - $transaction->total_amount,
    'payment_method' => $transaction->payment_method,

    'cashier' => $transaction->processedBy->full_name,
];
```

---

## Key Relationships

```php
// BillingTransaction → Medical Record
$transaction->medicalRecord      // BelongsTo

// BillingTransaction → Billing Items
$transaction->billingItems       // HasMany

// BillingTransaction → User (account owner)
$transaction->user               // BelongsTo

// BillingTransaction → Cashier
$transaction->processedBy        // BelongsTo (User)

// BillingItem → Service
$billingItem->service            // BelongsTo

// BillingItem → Hospital Drug
$billingItem->hospitalDrug       // BelongsTo
```

---

## Form Field Mappings

### Payment Form → billing_transactions

| Form Field | Column | Notes |
|------------|--------|-------|
| - | transaction_number | Auto-generated |
| - | transaction_date | today() |
| Emergency checkbox | is_emergency | ₱200 fee |
| Holiday checkbox | is_holiday | ₱150 fee |
| Sunday checkbox | is_sunday | ₱100 fee, auto-detected |
| After 5pm checkbox | is_after_5pm | ₱50 fee, auto-detected |
| Discount type | discount_type | Dropdown selection |
| Discount reason | discount_reason | Text input |
| Payment method | payment_method | Cash, GCash, etc. |
| Amount tendered | amount_paid | Cash received |

### Item Form → billing_items

| Form Field | Column | Notes |
|------------|--------|-------|
| Item type | item_type | professional_fee, service, drug, etc. |
| Service select | service_id | FK to services |
| Drug select | hospital_drug_id | FK to hospital_drugs |
| Custom description | item_description | For "other" type |
| Quantity | quantity | Integer |
| Unit price | unit_price | Auto-filled from service/drug |
| Total | total_price | quantity × unit_price |

---

## Status Flow

```
Medical Record                    Billing Transaction
──────────────                    ───────────────────
for_billing  ───────────────────→ (created)
                                      │
                                      ▼
                                  payment_status: pending
                                      │
                                      ▼ (payment processed)
completed    ←───────────────────  payment_status: paid
```
