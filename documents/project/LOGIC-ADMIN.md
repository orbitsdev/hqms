# Admin Module Logic

## Overview

The Admin Console manages system-wide settings and master data. Admins can:
- Manage users (all roles)
- Configure consultation types
- Manage billable services
- Manage hospital drug inventory
- Configure system settings

---

## Tables Used

| Table | Purpose in Admin Module |
|-------|-------------------------|
| `users` | All user accounts |
| `personal_information` | User profile data |
| `roles` | Spatie Permission roles |
| `consultation_types` | Service categories |
| `services` | Billable services/fees |
| `hospital_drugs` | Drug inventory |
| `system_settings` | Application configuration |
| `consultation_type_user` | Doctor-specialty pivot |

---

## 1. User Management

### 1.1 User Roles

**Package:** Spatie Laravel Permission

```php
// Available roles
'patient'   // Can book appointments, view records
'nurse'     // Manage queue, triage, vitals
'doctor'    // Examine, diagnose, prescribe
'cashier'   // Process billing, payments
'admin'     // Full system access
```

### 1.2 Creating Users

**Component:** `Admin\UserManagement`

```php
// Create user account
$user = User::create([
    'first_name' => 'Maria',
    'middle_name' => 'Cruz',
    'last_name' => 'Santos',
    'email' => 'maria@hospital.com',
    'phone' => '09171234567',
    'password' => Hash::make($password),
    'email_verified_at' => now(),  // Pre-verified by admin
]);

// Assign role
$user->assignRole('nurse');
```

### 1.3 Personal Information

When admin creates a user, personal information is also created:

```php
// personal_information table
PersonalInformation::updateOrCreate(
    ['user_id' => $user->id],
    [
        'first_name' => $this->firstName,
        'last_name' => $this->lastName,
        'middle_name' => $this->middleName,
        'date_of_birth' => $this->dateOfBirth,
        'gender' => $this->gender,
        'phone_number' => $this->phoneNumber,
    ]
);
```

### 1.4 Doctor Specialization

Doctors must be assigned to consultation types:

```php
// consultation_type_user pivot table
if ($this->role === 'doctor') {
    $user->consultationTypes()->sync($this->selectedConsultationTypes);
}

// This determines which queue the doctor sees
// In Doctor\Queue component:
$consultationTypeIds = auth()->user()->consultationTypes->pluck('id');

$queues = Queue::whereIn('consultation_type_id', $consultationTypeIds)
    ->whereDate('queue_date', today())
    ->get();
```

### 1.5 User Status Management

```php
// Soft delete (deactivate)
$user->delete();  // Sets deleted_at timestamp

// Restore
$user = User::withTrashed()->find($userId);
$user->restore();

// Query active/inactive
User::query()  // Active users
User::onlyTrashed()  // Inactive users
User::withTrashed()  // All users
```

### 1.6 Role-Based Filtering

```php
$users = User::query()
    ->with(['roles', 'personalInformation'])
    ->when($this->statusFilter === 'inactive', fn ($q) => $q->onlyTrashed())
    ->when($this->roleFilter, fn ($q) => $q->role($this->roleFilter))
    ->when($this->search, fn ($q) => $q
        ->where(function ($query) {
            $query->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%");
        }))
    ->orderByDesc('created_at')
    ->paginate(15);
```

### 1.7 Role Statistics

```php
$roleCounts = [
    'all' => User::count(),
    'patient' => User::role('patient')->count(),
    'doctor' => User::role('doctor')->count(),
    'nurse' => User::role('nurse')->count(),
    'cashier' => User::role('cashier')->count(),
    'admin' => User::role('admin')->count(),
];
```

---

## 2. Consultation Types

### 2.1 Consultation Type Structure

**Migration:** `2026_01_19_062101_create_consultation_types_table.php`

```php
// consultation_types table
ConsultationType::create([
    'name' => 'Pediatrics',
    'short_name' => 'PED',           // Used for queue numbers: PED-001
    'code' => 'pedia',               // Internal code
    'description' => 'Child healthcare services',
    'base_consultation_fee' => 500.00,
    'is_active' => true,
]);
```

### 2.2 Usage in Queue Numbers

```php
// Queue number format: {short_name}-{number}
$queueNumber = $consultationType->short_name . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
// Examples: PED-001, OB-015, GEN-042
```

### 2.3 Usage in Professional Fees

```php
// In billing, consultation type determines fee
$feeServiceName = match ($consultationType->code) {
    'ob' => 'Professional Fee - OB',
    'pedia' => 'Professional Fee - Pediatrics',
    default => 'Professional Fee - General',
};
```

### 2.4 Activating/Deactivating

```php
// Only active consultation types appear in booking
ConsultationType::where('is_active', true)->get();

// Deactivated types don't accept new appointments
// But existing records and history are preserved
```

---

## 3. Service Management

### 3.1 Service Structure

**Migration:** `2026_01_19_090007_create_services_table.php`

**Component:** `Admin\ServiceManagement`

```php
// services table
Service::create([
    'service_name' => 'Professional Fee - Pediatrics',
    'category' => 'consultation',
    'description' => 'Doctor consultation fee for pediatrics',
    'base_price' => 500.00,
    'is_active' => true,
    'display_order' => 1,  // Sort order in lists
]);
```

### 3.2 Service Categories

```php
'category' => enum [
    'consultation',  // Professional fees
    'ultrasound',    // Imaging services
    'procedure',     // Medical procedures
    'laboratory',    // Lab tests
    'other',         // Miscellaneous
]
```

### 3.3 Category Examples

| Category | Examples |
|----------|----------|
| consultation | Professional Fee - OB, Professional Fee - Pedia |
| ultrasound | Pregnancy Ultrasound, Pelvic Ultrasound |
| procedure | Circumcision, Minor Surgery, Wound Care |
| laboratory | CBC, Urinalysis, Blood Typing |
| other | Medical Certificate, Room Charge |

### 3.4 Display Order

Services are sorted by category, then display_order, then name:

```php
Service::query()
    ->orderBy('category')
    ->orderBy('display_order')
    ->orderBy('service_name')
    ->get();
```

### 3.5 Usage in Billing

```php
// Cashier selects service when adding billing item
$service = Service::where('is_active', true)
    ->orderBy('category')
    ->orderBy('display_order')
    ->get();

// Selected service auto-fills price
$billingItem = [
    'type' => 'service',
    'description' => $service->service_name,
    'service_id' => $service->id,
    'unit_price' => $service->base_price,
];
```

---

## 4. Hospital Drug Management

### 4.1 Drug Structure

**Migration:** `2026_01_19_090005_create_hospital_drugs_table.php`

**Component:** `Admin\HospitalDrugManagement`

```php
// hospital_drugs table
HospitalDrug::create([
    'drug_name' => 'Paracetamol 500mg',
    'generic_name' => 'Paracetamol',
    'description' => 'Pain reliever and fever reducer',
    'unit_price' => 5.00,
    'is_active' => true,
]);
```

### 4.2 Drug Fields

| Field | Purpose | Example |
|-------|---------|---------|
| drug_name | Display name | Biogesic 500mg |
| generic_name | Generic equivalent | Paracetamol |
| description | Usage notes | For fever and pain |
| unit_price | Price per unit | ₱5.00 |
| is_active | Availability | true/false |

### 4.3 Usage in Prescriptions

```php
// Doctor prescribes hospital drug
Prescription::create([
    'medical_record_id' => $recordId,
    'hospital_drug_id' => $drug->id,          // FK to hospital_drugs
    'medication_name' => $drug->drug_name,    // Copied for history
    'dosage' => '500mg',
    'frequency' => 'Every 4 hours',
    'quantity' => 10,
    'is_hospital_drug' => true,               // Flag for billing
]);
```

### 4.4 Usage in Billing

```php
// Cashier auto-loads hospital drugs from prescriptions
$prescriptions = $medicalRecord->prescriptions()
    ->where('is_hospital_drug', true)
    ->with('hospitalDrug')
    ->get();

foreach ($prescriptions as $rx) {
    $billingItems[] = [
        'type' => 'drug',
        'description' => $rx->hospitalDrug->drug_name,
        'drug_id' => $rx->hospital_drug_id,
        'quantity' => $rx->quantity,
        'unit_price' => $rx->hospitalDrug->unit_price,
        'total_price' => $rx->quantity * $rx->hospitalDrug->unit_price,
    ];
}
```

---

## 5. System Settings

### 5.1 Settings Structure

**Migration:** `2026_01_19_063333_create_system_settings_table.php`

```php
// system_settings table (key-value store)
SystemSetting::set('max_advance_booking_days', 30);
SystemSetting::set('allow_same_day_booking', true);
SystemSetting::set('clinic_open_time', '08:00');
SystemSetting::set('clinic_close_time', '17:00');
```

### 5.2 Common Settings

| Key | Type | Default | Usage |
|-----|------|---------|-------|
| max_advance_booking_days | int | 30 | How far ahead patients can book |
| allow_same_day_booking | bool | true | Can patients book for today |
| clinic_open_time | string | 08:00 | Operating hours start |
| clinic_close_time | string | 17:00 | Operating hours end |

### 5.3 Retrieving Settings

```php
// Get setting with default fallback
$maxDays = SystemSetting::get('max_advance_booking_days', 30);

// Boolean settings
$allowSameDay = (bool) SystemSetting::get('allow_same_day_booking', true);
```

### 5.4 Usage in Booking

```php
// Patient\BookAppointment
protected function buildAvailableDates(): array
{
    $maxAdvanceDays = (int) SystemSetting::get('max_advance_booking_days', 30);
    $allowSameDay = (bool) SystemSetting::get('allow_same_day_booking', true);

    $startDate = $allowSameDay ? today() : today()->addDay();

    // Build available dates based on settings
    for ($i = 0; $i < min(14, $maxAdvanceDays); $i++) {
        // ...
    }
}
```

---

## 6. Dashboard Statistics

### 6.1 Admin Dashboard Stats

**Component:** `Admin\Dashboard`

```php
$stats = [
    'total_users' => User::count(),
    'active_patients' => User::role('patient')->count(),
    'staff_count' => User::whereHas('roles', function ($q) {
        $q->whereIn('name', ['nurse', 'doctor', 'cashier', 'admin']);
    })->count(),

    'today_appointments' => Appointment::whereDate('appointment_date', today())->count(),
    'pending_appointments' => Appointment::where('status', 'pending')->count(),

    'today_revenue' => BillingTransaction::whereDate('transaction_date', today())
        ->where('payment_status', 'paid')
        ->sum('total_amount'),

    'active_services' => Service::where('is_active', true)->count(),
    'active_drugs' => HospitalDrug::where('is_active', true)->count(),
];
```

---

## Key Relationships

```php
// User → Roles (Spatie)
$user->roles                    // BelongsToMany
$user->hasRole('doctor')        // Check role

// User → Personal Information
$user->personalInformation      // HasOne

// User → Consultation Types (for doctors)
$user->consultationTypes        // BelongsToMany

// ConsultationType → Users (doctors)
$consultationType->doctors      // BelongsToMany

// Service → Billing Items
$service->billingItems          // HasMany

// HospitalDrug → Prescriptions
$hospitalDrug->prescriptions    // HasMany

// HospitalDrug → Billing Items
$hospitalDrug->billingItems     // HasMany
```

---

## Form Field Mappings

### User Form → users + personal_information

| Form Field | users Column | personal_information Column |
|------------|--------------|----------------------------|
| Email | email | - |
| Password | password | - |
| First Name | first_name | first_name |
| Middle Name | middle_name | middle_name |
| Last Name | last_name | last_name |
| Phone | phone | phone_number |
| Date of Birth | - | date_of_birth |
| Gender | - | gender |
| Role | via Spatie | - |
| Consultation Types | - | via pivot (doctors) |

### Service Form → services

| Form Field | Column | Validation |
|------------|--------|------------|
| Service Name | service_name | required, max:255 |
| Category | category | required, in:consultation,ultrasound,procedure,laboratory,other |
| Description | description | nullable |
| Base Price | base_price | required, numeric, min:0 |
| Is Active | is_active | boolean |
| Display Order | display_order | integer |

### Drug Form → hospital_drugs

| Form Field | Column | Validation |
|------------|--------|------------|
| Drug Name | drug_name | required, max:255 |
| Generic Name | generic_name | nullable |
| Description | description | nullable |
| Unit Price | unit_price | required, numeric, min:0 |
| Is Active | is_active | boolean |

---

## Access Control

### Route Protection

```php
// routes/web.php
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', Admin\Dashboard::class)->name('admin.dashboard');
    Route::get('/users', Admin\UserManagement::class)->name('admin.users');
    Route::get('/services', Admin\ServiceManagement::class)->name('admin.services');
    Route::get('/drugs', Admin\HospitalDrugManagement::class)->name('admin.drugs');
});
```

### Middleware

```php
// Spatie Permission middleware
'role:admin'  // Must have admin role
```

---

## Data Integrity

### Soft Deletes

Users are soft-deleted (deactivated) rather than permanently removed:

```php
// In User model
use SoftDeletes;

// Deactivate
$user->delete();

// This preserves:
// - Historical records (medical_records.doctor_id still valid)
// - Audit trails (billing_transactions.processed_by)
// - Patient data integrity
```

### Cascade Rules

```php
// services.id used in billing_items
$table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');

// hospital_drugs.id used in prescriptions and billing_items
$table->foreignId('hospital_drug_id')->nullable()->constrained()->onDelete('set null');

// When service/drug is deleted, billing records remain with null FK
// But item_description preserves the name for historical accuracy
```
