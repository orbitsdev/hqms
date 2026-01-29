# Patient Module Logic

## Overview

The Patient Portal allows patients to:
- Complete their profile (required before booking)
- Book appointments for themselves or dependents
- Track queue status in real-time
- View medical records and visit history

---

## Tables Used

| Table | Purpose in Patient Module |
|-------|---------------------------|
| `users` | Patient account (login, auth) |
| `personal_information` | Patient's own profile |
| `appointments` | Booking requests |
| `queues` | Queue position tracking |
| `medical_records` | Visit history |
| `consultation_types` | Available services |

---

## 1. User Registration & Profile

### 1.1 User Creation

When patient registers:

```php
// users table
$user = User::create([
    'first_name' => 'Maria',
    'middle_name' => 'Cruz',
    'last_name' => 'Santos',
    'email' => 'maria@example.com',
    'phone' => '09171234567',
    'password' => Hash::make('password'),
]);

$user->assignRole('patient');
```

### 1.2 Personal Information (Profile Completion)

**Migration:** `2026_01_19_060507_create_personal_information_table.php`

Patients must complete profile before booking. This stores the **account owner's** information.

```php
// personal_information table
PersonalInformation::create([
    'user_id' => $user->id,
    'first_name' => 'Maria',
    'middle_name' => 'Cruz',
    'last_name' => 'Santos',
    'date_of_birth' => '1990-05-15',
    'gender' => 'female',
    'phone' => '09171234567',

    // Address (Philippine format)
    'province' => 'Metro Manila',
    'municipality' => 'Quezon City',
    'barangay' => 'Loyola Heights',
    'street' => '123 Katipunan Ave',

    // Emergency contact (required)
    'emergency_contact_name' => 'Juan Santos',
    'emergency_contact_phone' => '09181234567',
]);
```

### 1.3 Profile Completion Check

**Middleware:** `EnsurePersonalInfoComplete`

```php
// User model method
public function hasCompletePersonalInformation(): bool
{
    $info = $this->personalInformation;

    if (!$info) return false;

    // Required fields
    $required = [
        $info->first_name,
        $info->last_name,
        $info->phone,
        $info->date_of_birth,
        $info->gender,
        $info->province,
        $info->municipality,
        $info->barangay,
        $info->street,
        $info->emergency_contact_name,
        $info->emergency_contact_phone,
    ];

    foreach ($required as $value) {
        if (!filled($value)) return false;
    }

    return true;
}
```

**Usage:** Patient is redirected to profile page until complete.

---

## 2. Appointment Booking

### 2.1 Consultation Types

**Migration:** `2026_01_19_062101_create_consultation_types_table.php`

```php
// consultation_types table
[
    'id' => 1,
    'name' => 'Pediatrics',
    'short_name' => 'PED',           // Used for queue numbers: PED-001
    'description' => 'Child healthcare',
    'base_consultation_fee' => 500.00,
    'is_active' => true,
]
```

**Form Display:**
```blade
@foreach($consultationTypes as $type)
    <button wire:click="selectConsultationType({{ $type->id }})">
        {{ $type->name }}
        <span>₱{{ number_format($type->base_consultation_fee, 2) }}</span>
    </button>
@endforeach
```

### 2.2 Booking for Self vs Dependent

**Migration:** `2026_01_19_090002_create_appointments_table.php`

The `relationship_to_account` column determines if booking is for self or dependent:

```php
// When booking for SELF
// Data from personal_information is used
$appointment = Appointment::create([
    'user_id' => auth()->id(),  // Account owner (for notifications)

    // Patient info copied from personal_information
    'patient_first_name' => $info->first_name,
    'patient_middle_name' => $info->middle_name,
    'patient_last_name' => $info->last_name,
    'patient_date_of_birth' => $info->date_of_birth,
    'patient_gender' => $info->gender,
    'patient_phone' => $info->phone,
    'patient_province' => $info->province,
    'patient_municipality' => $info->municipality,
    'patient_barangay' => $info->barangay,
    'patient_street' => $info->street,

    'relationship_to_account' => 'self',  // <-- KEY FIELD

    'consultation_type_id' => $consultationTypeId,
    'appointment_date' => '2026-01-30',
    'chief_complaints' => 'Fever and cough for 3 days',
    'status' => 'pending',
    'source' => 'online',
]);
```

```php
// When booking for DEPENDENT (e.g., child)
// Patient info entered manually in form
$appointment = Appointment::create([
    'user_id' => auth()->id(),  // Parent's ID (receives notifications)

    // Child's info (different from account owner)
    'patient_first_name' => 'Juan Jr.',
    'patient_middle_name' => 'Cruz',
    'patient_last_name' => 'Santos',
    'patient_date_of_birth' => '2020-03-10',
    'patient_gender' => 'male',

    // Contact uses parent's info
    'patient_phone' => $info->phone,
    'patient_province' => $info->province,
    'patient_municipality' => $info->municipality,
    'patient_barangay' => $info->barangay,
    'patient_street' => $info->street,

    'relationship_to_account' => 'child',  // <-- KEY FIELD

    'consultation_type_id' => $consultationTypeId,
    'appointment_date' => '2026-01-30',
    'chief_complaints' => 'Child has rashes',
    'status' => 'pending',
    'source' => 'online',
]);
```

### 2.3 Relationship Options

```php
'relationship_to_account' => enum [
    'self',     // Booking for themselves
    'child',    // Parent booking for child
    'spouse',   // Booking for husband/wife
    'parent',   // Booking for elderly parent
    'sibling',  // Booking for brother/sister
    'other',    // Other relationship
]
```

### 2.4 Available Dates Logic

**Component:** `Patient\BookAppointment`

Dates are filtered based on:
1. `SystemSetting::get('max_advance_booking_days')` - How far ahead can book
2. `SystemSetting::get('allow_same_day_booking')` - Can book today?
3. `ConsultationType::isAcceptingAppointments($date)` - Doctor availability

```php
protected function buildAvailableDates(): array
{
    $maxAdvanceDays = (int) SystemSetting::get('max_advance_booking_days', 30);
    $allowSameDay = (bool) SystemSetting::get('allow_same_day_booking', true);

    $startDate = $allowSameDay ? today() : today()->addDay();

    $dates = [];
    for ($i = 0; $i < 14; $i++) {
        $date = $startDate->copy()->addDays($i);
        $dateString = $date->toDateString();

        // Check if consultation type accepts appointments on this date
        $available = $this->consultationType->isAcceptingAppointments($dateString);

        $dates[] = [
            'date' => $dateString,
            'formatted' => $date->format('M d, Y'),
            'available' => $available,
        ];
    }

    return $dates;
}
```

### 2.5 Appointment Notification

When appointment is created, nurses are notified:

```php
$nurses = User::role('nurse')->get();

Notification::send($nurses, new GenericNotification([
    'type' => 'appointment.requested',
    'title' => 'New Appointment Request',
    'message' => "{$patient['first_name']} {$patient['last_name']} requested an appointment.",
    'appointment_id' => $appointment->id,
]));
```

---

## 3. Queue Tracking

### 3.1 Active Queue Display

**Component:** `Patient\ActiveQueue`

After check-in, patient can track their queue:

```php
public function activeQueue(): ?Queue
{
    return Queue::query()
        ->whereHas('appointment', fn ($q) =>
            $q->where('user_id', Auth::id())  // Account owner
        )
        ->whereDate('queue_date', today())
        ->whereIn('status', ['waiting', 'called', 'serving'])
        ->with(['consultationType', 'appointment'])
        ->first();
}
```

### 3.2 Queue Position Calculation

```php
public function queuePosition(): ?int
{
    $queue = $this->activeQueue;

    if (!$queue || $queue->status !== 'waiting') {
        return null;
    }

    // Count how many are ahead in same consultation type
    return Queue::query()
        ->where('consultation_type_id', $queue->consultation_type_id)
        ->whereDate('queue_date', today())
        ->where('status', 'waiting')
        ->where('queue_number', '<', $queue->queue_number)
        ->count() + 1;  // +1 because position starts at 1
}
```

### 3.3 Real-time Updates

**Event:** `QueueUpdated`

When queue status changes, patient receives real-time update:

```php
// In Nurse\TodayQueue when calling patient
broadcast(new QueueUpdated($queue));

// Patient\ActiveQueue listens
#[On('echo-private:queue.patient.{userId},queue.updated')]
public function refreshOnQueueUpdate(): void
{
    // Component re-renders automatically
}
```

### 3.4 Queue Status Display

```blade
@if($activeQueue->status === 'serving')
    <span class="animate-pulse">Now Being Served</span>
@elseif($activeQueue->status === 'called')
    <span class="animate-pulse">Please Proceed to Nurse Station</span>
@else
    <span>Waiting - Position {{ $queuePosition }}</span>
@endif
```

---

## 4. Medical Records

### 4.1 Viewing Records

**Component:** `Patient\MedicalRecords`

Patients see only their own records (as account owner):

```php
$records = MedicalRecord::query()
    ->where('user_id', Auth::id())  // Account owner
    ->whereIn('status', ['completed', 'for_billing'])
    ->with(['consultationType', 'doctor.personalInformation'])
    ->orderByDesc('visit_date')
    ->paginate(10);
```

**Note:** This includes records for dependents (children, spouse, etc.) because `user_id` is the account owner, not the patient.

### 4.2 Record Details

**Component:** `Patient\MedicalRecordShow`

```php
public function mount(MedicalRecord $medicalRecord): void
{
    // Security: Ensure record belongs to current user
    if ($medicalRecord->user_id !== Auth::id()) {
        abort(403);
    }

    $this->medicalRecordId = $medicalRecord->id;
}
```

### 4.3 Information Displayed

```blade
{{-- Patient Info (from self-contained record) --}}
<p>Name: {{ $record->patient_full_name }}</p>
<p>Age: {{ $record->patient_age }} years old</p>
<p>Gender: {{ ucfirst($record->patient_gender) }}</p>

{{-- Vital Signs --}}
<p>Temperature: {{ $record->temperature }}°C</p>
<p>Blood Pressure: {{ $record->blood_pressure }}</p>
<p>Weight: {{ $record->weight }} kg</p>

{{-- Diagnosis --}}
<p>Complaints: {{ $record->effective_chief_complaints }}</p>
<p>Diagnosis: {{ $record->diagnosis }}</p>
<p>Plan: {{ $record->plan }}</p>

{{-- Prescriptions --}}
@foreach($record->prescriptions as $rx)
    <p>{{ $rx->medication_name }} - {{ $rx->dosage }} {{ $rx->frequency }}</p>
@endforeach

{{-- Billing --}}
@if($record->billingTransaction)
    <p>Total Paid: ₱{{ number_format($record->billingTransaction->total_amount, 2) }}</p>
@endif
```

---

## 5. Appointments List

### 5.1 Viewing Appointments

**Component:** `Patient\Appointments`

```php
$appointments = Appointment::query()
    ->where('user_id', Auth::id())
    ->with(['consultationType', 'doctor'])
    ->orderByDesc('appointment_date')
    ->paginate(10);
```

### 5.2 Appointment Status Display

```php
$statusVariant = match ($appointment->status) {
    'approved' => 'success',   // Green badge
    'pending' => 'warning',     // Yellow badge
    'completed' => 'success',
    'cancelled' => 'danger',    // Red badge
    'no_show' => 'danger',
    default => 'default'
};
```

### 5.3 Cancellation

Patients can cancel pending/approved appointments:

```php
public function cancelAppointment(): void
{
    if (!in_array($this->appointment->status, ['pending', 'approved'])) {
        return;  // Can't cancel if already checked in
    }

    $this->appointment->update([
        'status' => 'cancelled',
        'cancellation_reason' => $this->cancellationReason,
    ]);
}
```

---

## 6. Dashboard Summary

### 6.1 Stats Computed

**Component:** `Patient\Dashboard`

```php
public function stats(): array
{
    return [
        'total_visits' => MedicalRecord::where('user_id', Auth::id())
            ->whereIn('status', ['completed', 'for_billing'])
            ->count(),

        'pending_appointments' => Appointment::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->count(),

        'upcoming_appointments' => Appointment::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->where('appointment_date', '>=', today())
            ->count(),
    ];
}
```

### 6.2 Active Queue on Dashboard

If patient has active queue, prominently displayed:

```php
public function activeQueue()
{
    return Queue::query()
        ->whereHas('appointment', fn ($q) => $q->where('user_id', Auth::id()))
        ->whereDate('queue_date', today())
        ->whereIn('status', ['waiting', 'called', 'serving'])
        ->first();
}
```

---

## Key Relationships Used

```php
// User → Personal Information
$user->personalInformation  // HasOne

// User → Appointments (as account owner)
$user->appointments         // HasMany

// Appointment → Queue
$appointment->queue         // HasOne (created on check-in)

// Appointment → Consultation Type
$appointment->consultationType  // BelongsTo

// Queue → Medical Record
$queue->medicalRecord       // HasOne (created during triage)

// Medical Record → Prescriptions
$medicalRecord->prescriptions   // HasMany

// Medical Record → Billing
$medicalRecord->billingTransaction  // HasOne
```

---

## Form Field Mappings

### Profile Form → personal_information

| Form Field | Column | Validation |
|------------|--------|------------|
| First Name | first_name | required, max:255 |
| Middle Name | middle_name | nullable |
| Last Name | last_name | required, max:255 |
| Date of Birth | date_of_birth | required, date, before:today |
| Gender | gender | required, in:male,female |
| Phone | phone | required, max:20 |
| Province | province | required |
| Municipality | municipality | required |
| Barangay | barangay | required |
| Street | street | required |
| Emergency Contact | emergency_contact_name | required |
| Emergency Phone | emergency_contact_phone | required |

### Booking Form → appointments

| Form Field | Column | Notes |
|------------|--------|-------|
| Patient Type | relationship_to_account | self or dependent |
| First Name | patient_first_name | From profile if self |
| Last Name | patient_last_name | From profile if self |
| DOB | patient_date_of_birth | From profile if self |
| Gender | patient_gender | From profile if self |
| Consultation | consultation_type_id | Selected service |
| Date | appointment_date | From available dates |
| Complaints | chief_complaints | Text input |
