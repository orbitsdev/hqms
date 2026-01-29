# Nurse Module Logic

## Overview

The Nurse Station is the central hub for patient flow management:
- Approve/reject appointment requests
- Check-in patients and create queue entries
- Conduct patient triage (interview, vital signs)
- Forward patients to doctors
- Manage walk-in registrations

---

## Tables Used

| Table | Purpose in Nurse Module |
|-------|-------------------------|
| `appointments` | Approve, reject, check-in |
| `queues` | Queue management (call, serve, forward) |
| `medical_records` | Create and fill during triage |
| `doctor_schedules` | View doctor availability |
| `consultation_types` | Service categories |
| `users` | Doctor assignments |

---

## 1. Doctor Schedules

### 1.1 Schedule Structure

**Migration:** `2026_01_19_080031_create_doctor_schedules_table.php`

```php
// doctor_schedules table
Schema::create('doctor_schedules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();           // Doctor
    $table->foreignId('consultation_type_id')->constrained();
    $table->enum('schedule_type', ['regular', 'exception']);

    // For REGULAR schedules (weekly pattern)
    $table->unsignedTinyInteger('day_of_week')->nullable();
    // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

    // For EXCEPTION schedules (specific dates)
    $table->date('date')->nullable();

    $table->time('start_time');
    $table->time('end_time');
    $table->integer('max_patients')->default(20);
    $table->boolean('is_available')->default(true);  // For exceptions: off/on
});
```

### 1.2 Day of Week Mapping

```php
// day_of_week values
0 => 'Sunday',
1 => 'Monday',
2 => 'Tuesday',
3 => 'Wednesday',
4 => 'Thursday',
5 => 'Friday',
6 => 'Saturday',
```

### 1.3 Regular vs Exception Schedules

**Regular Schedule (weekly pattern):**
```php
// Dr. Santos works Monday, Wednesday, Friday for Pediatrics
DoctorSchedule::create([
    'user_id' => $doctor->id,
    'consultation_type_id' => 1,  // Pediatrics
    'schedule_type' => 'regular',
    'day_of_week' => 1,           // Monday
    'start_time' => '08:00',
    'end_time' => '12:00',
    'max_patients' => 20,
    'is_available' => true,
]);

DoctorSchedule::create([
    'user_id' => $doctor->id,
    'consultation_type_id' => 1,
    'schedule_type' => 'regular',
    'day_of_week' => 3,           // Wednesday
    'start_time' => '13:00',
    'end_time' => '17:00',
    'max_patients' => 15,
    'is_available' => true,
]);
```

**Exception Schedule (specific date override):**
```php
// Dr. Santos will be OFF on Jan 30, 2026 (normally a Wednesday)
DoctorSchedule::create([
    'user_id' => $doctor->id,
    'consultation_type_id' => 1,
    'schedule_type' => 'exception',
    'date' => '2026-01-30',
    'start_time' => '00:00',
    'end_time' => '00:00',
    'is_available' => false,      // NOT available
]);

// Dr. Santos adds extra hours on Jan 31, 2026 (normally off)
DoctorSchedule::create([
    'user_id' => $doctor->id,
    'consultation_type_id' => 1,
    'schedule_type' => 'exception',
    'date' => '2026-01-31',
    'start_time' => '09:00',
    'end_time' => '12:00',
    'is_available' => true,       // EXTRA availability
]);
```

### 1.4 Checking Availability

```php
// ConsultationType model
public function isAcceptingAppointments(string $date): bool
{
    $dayOfWeek = Carbon::parse($date)->dayOfWeek;

    // Check for exception on this date first
    $exception = $this->doctorSchedules()
        ->where('schedule_type', 'exception')
        ->where('date', $date)
        ->first();

    if ($exception) {
        return $exception->is_available;
    }

    // Check regular schedule for this day
    return $this->doctorSchedules()
        ->where('schedule_type', 'regular')
        ->where('day_of_week', $dayOfWeek)
        ->where('is_available', true)
        ->exists();
}
```

---

## 2. Appointment Management

### 2.1 Viewing Appointments

**Component:** `Nurse\Appointments`

```php
$appointments = Appointment::query()
    ->whereDate('appointment_date', $this->selectedDate)
    ->when($this->statusFilter, fn ($q) =>
        $q->where('status', $this->statusFilter)
    )
    ->with(['consultationType', 'user.personalInformation'])
    ->orderBy('appointment_time')
    ->paginate(15);
```

### 2.2 Approving Appointments

**Component:** `Nurse\AppointmentShow`

When nurse approves:

```php
public function approve(): void
{
    $this->appointment->update([
        'status' => 'approved',
        'approved_by' => auth()->id(),
        'approved_at' => now(),
        'appointment_time' => $this->appointmentTime,  // Nurse sets time
    ]);

    // Notify patient
    $this->appointment->user->notify(new GenericNotification([
        'type' => 'appointment.approved',
        'title' => 'Appointment Approved',
        'message' => "Your appointment on {$date} at {$time} has been approved.",
    ]));
}
```

### 2.3 Rejecting Appointments

```php
public function reject(): void
{
    $this->validate([
        'declineReason' => 'required|string|min:10',
    ]);

    $this->appointment->update([
        'status' => 'cancelled',
        'decline_reason' => $this->declineReason,
        'suggested_date' => $this->suggestedDate,  // Optional alternative
    ]);

    // Notify patient
    $this->appointment->user->notify(new GenericNotification([
        'type' => 'appointment.rejected',
        'title' => 'Appointment Not Available',
        'message' => $this->declineReason,
    ]));
}
```

### 2.4 Check-in Process

When patient arrives and nurse checks them in:

```php
public function checkIn(): void
{
    // Update appointment status
    $this->appointment->update([
        'status' => 'checked_in',
        'checked_in_at' => now(),
    ]);

    // Create queue entry
    $queue = Queue::create([
        'appointment_id' => $this->appointment->id,
        'user_id' => $this->appointment->user_id,
        'consultation_type_id' => $this->appointment->consultation_type_id,
        'queue_number' => $this->generateQueueNumber(),
        'queue_date' => today(),
        'session_number' => now()->hour < 12 ? 1 : 2,  // 1=AM, 2=PM
        'priority' => 'normal',
        'status' => 'waiting',
        'source' => $this->appointment->source,
    ]);

    // Broadcast queue update
    broadcast(new QueueUpdated($queue));
}
```

---

## 3. Queue Management

### 3.1 Queue Number Generation

**Migration:** `2026_01_19_090003_create_queues_table.php`

Queue numbers are sequential per consultation type per day:

```php
protected function generateQueueNumber(): int
{
    $lastQueue = Queue::query()
        ->where('consultation_type_id', $this->consultationTypeId)
        ->whereDate('queue_date', today())
        ->max('queue_number');

    return ($lastQueue ?? 0) + 1;
}

// Result: 1, 2, 3, ... (resets daily)
```

### 3.2 Formatted Queue Number

```php
// Queue model accessor
public function getFormattedNumberAttribute(): string
{
    return $this->consultationType->short_name . '-' . $this->queue_number;
}

// Examples: PED-001, OB-015, GEN-003
```

### 3.3 Queue Priority

```php
'priority' => enum ['normal', 'urgent', 'emergency']
```

**Usage:**
- `normal` - Regular queue order
- `urgent` - Prioritized (pregnant with complications, etc.)
- `emergency` - Immediate attention

```php
// Sorting queue by priority then number
Queue::query()
    ->today()
    ->waiting()
    ->orderByRaw("CASE priority
        WHEN 'emergency' THEN 1
        WHEN 'urgent' THEN 2
        WHEN 'normal' THEN 3
        ELSE 4 END")
    ->orderBy('queue_number')
    ->get();
```

### 3.4 Calling a Patient

**Component:** `Nurse\TodayQueue`

```php
public function callPatient(int $queueId): void
{
    $queue = Queue::findOrFail($queueId);

    $queue->update([
        'status' => 'called',
        'called_at' => now(),
    ]);

    // Broadcast to display monitors
    broadcast(new QueueUpdated($queue));

    // Notify patient
    $queue->user->notify(new GenericNotification([
        'type' => 'queue.called',
        'title' => 'Your Number is Called',
        'message' => "Queue {$queue->formatted_number} - Please proceed to the nurse station.",
    ]));
}
```

### 3.5 Starting Service (Triage)

```php
public function startServing(int $queueId): void
{
    $queue = Queue::findOrFail($queueId);

    $queue->update([
        'status' => 'serving',
        'serving_started_at' => now(),
        'served_by' => auth()->id(),
    ]);

    // Create medical record
    $medicalRecord = $this->createMedicalRecord($queue);

    broadcast(new QueueUpdated($queue));

    // Open interview modal
    $this->openInterviewModal($queue->id);
}
```

### 3.6 Skip Patient (No Show)

```php
public function skipPatient(int $queueId): void
{
    $queue = Queue::findOrFail($queueId);

    $queue->update([
        'status' => 'skipped',
        'serving_ended_at' => now(),
        'notes' => 'Patient did not respond when called',
    ]);

    broadcast(new QueueUpdated($queue));
}
```

### 3.7 Requeue (Called Back)

```php
public function requeuePatient(int $queueId): void
{
    $queue = Queue::findOrFail($queueId);

    // Generate new queue number
    $newNumber = $this->generateQueueNumber();

    $queue->update([
        'status' => 'waiting',
        'queue_number' => $newNumber,
        'called_at' => null,
    ]);

    broadcast(new QueueUpdated($queue));
}
```

---

## 4. Patient Interview (Triage)

### 4.1 Medical Record Creation

**Migration:** `2026_01_19_090004_create_medical_records_table.php`

When nurse starts serving, medical record is created:

```php
protected function createMedicalRecord(Queue $queue): MedicalRecord
{
    $appointment = $queue->appointment;

    return MedicalRecord::create([
        // Record identification
        'record_number' => MedicalRecord::generateRecordNumber(),

        // Relations
        'user_id' => $queue->user_id,
        'consultation_type_id' => $queue->consultation_type_id,
        'appointment_id' => $appointment?->id,
        'queue_id' => $queue->id,
        'nurse_id' => auth()->id(),

        // Patient info copied from appointment
        'patient_first_name' => $appointment->patient_first_name,
        'patient_middle_name' => $appointment->patient_middle_name,
        'patient_last_name' => $appointment->patient_last_name,
        'patient_date_of_birth' => $appointment->patient_date_of_birth,
        'patient_gender' => $appointment->patient_gender,
        'patient_province' => $appointment->patient_province,
        'patient_municipality' => $appointment->patient_municipality,
        'patient_barangay' => $appointment->patient_barangay,
        'patient_street' => $appointment->patient_street,
        'patient_contact_number' => $appointment->patient_phone,

        // Visit info
        'visit_date' => today(),
        'time_in' => now(),
        'time_in_period' => now()->hour < 12 ? 'am' : 'pm',
        'visit_type' => $this->determineVisitType($appointment),

        // Chief complaints from appointment
        'chief_complaints_initial' => $appointment->chief_complaints,

        'status' => 'in_progress',
    ]);
}
```

### 4.2 Visit Type Determination

```php
protected function determineVisitType($appointment): string
{
    // Check if patient has previous records
    $hasHistory = MedicalRecord::query()
        ->where('patient_first_name', $appointment->patient_first_name)
        ->where('patient_last_name', $appointment->patient_last_name)
        ->where('patient_date_of_birth', $appointment->patient_date_of_birth)
        ->exists();

    return $hasHistory ? 'old' : 'new';
}

// visit_type: 'new' | 'old' | 'revisit'
```

### 4.3 Interview Form (5 Steps)

**Component:** `Nurse\TodayQueue` (Interview Modal)

**Step 1: Patient Information Verification**
```php
// Verify/update patient details
'patient_marital_status' => 'single',
'patient_occupation' => 'Student',
'patient_religion' => 'Catholic',

// Companion (for minors)
'companion_name' => 'Maria Santos',
'companion_contact' => '09171234567',
'companion_relationship' => 'Mother',
```

**Step 2: Medical Background**
```php
'patient_blood_type' => 'O+',
'patient_allergies' => 'Penicillin',
'patient_chronic_conditions' => 'Asthma',

'emergency_contact_name' => 'Juan Santos',
'emergency_contact_phone' => '09181234567',
```

**Step 3: Chief Complaints Update**
```php
// Nurse can update/elaborate complaints
'chief_complaints_updated' => 'Fever for 3 days, highest at 39°C. With cough and colds. No vomiting.',
```

**Step 4: Vital Signs**
```php
// General vitals
'temperature' => 37.5,           // °C
'blood_pressure' => '120/80',    // mmHg
'cardiac_rate' => 80,            // bpm
'respiratory_rate' => 18,        // breaths/min
'weight' => 25.5,                // kg
'height' => 110,                 // cm

// Pediatric specific
'head_circumference' => 50,      // cm
'chest_circumference' => 52,     // cm

// OB specific
'fetal_heart_tone' => 140,       // bpm
'fundal_height' => 28,           // cm
'last_menstrual_period' => '2025-07-15',

'vital_signs_recorded_at' => now(),
```

**Step 5: Service Type Selection**
```php
'service_type' => 'checkup',     // 'checkup' | 'admission'
'service_category' => 'non-surgical',  // For admissions
'ob_type' => 'prenatal',         // For OB: 'prenatal' | 'post-natal'
```

### 4.4 Vital Signs Alert System

```php
// Abnormal vital signs detection
public function getVitalAlerts(MedicalRecord $record): array
{
    $alerts = [];

    // Temperature (normal: 36.1-37.2°C)
    if ($record->temperature && $record->temperature > 37.5) {
        $alerts[] = [
            'type' => 'warning',
            'message' => "Fever: {$record->temperature}°C",
        ];
    }
    if ($record->temperature && $record->temperature > 38.5) {
        $alerts[] = [
            'type' => 'danger',
            'message' => "High Fever: {$record->temperature}°C",
        ];
    }

    // Blood pressure (normal: 90/60 - 120/80)
    if ($record->blood_pressure) {
        [$systolic, $diastolic] = explode('/', $record->blood_pressure);
        if ($systolic > 140 || $diastolic > 90) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "High BP: {$record->blood_pressure}",
            ];
        }
    }

    // Heart rate (normal: 60-100 bpm)
    if ($record->cardiac_rate && $record->cardiac_rate > 100) {
        $alerts[] = [
            'type' => 'warning',
            'message' => "Tachycardia: {$record->cardiac_rate} bpm",
        ];
    }

    return $alerts;
}
```

---

## 5. Forward to Doctor

### 5.1 Doctor Assignment

After triage, nurse forwards patient to available doctor:

```php
public function forwardToDoctor(int $queueId, int $doctorId): void
{
    $queue = Queue::findOrFail($queueId);
    $record = $queue->medicalRecord;

    // Update queue
    $queue->update([
        'status' => 'waiting',  // Waiting for doctor
        'doctor_id' => $doctorId,
        'serving_ended_at' => now(),
    ]);

    // Update medical record
    $record->update([
        'doctor_id' => $doctorId,
    ]);

    broadcast(new QueueUpdated($queue));

    // Notify doctor
    User::find($doctorId)->notify(new GenericNotification([
        'type' => 'patient.forwarded',
        'title' => 'Patient Forwarded',
        'message' => "Patient {$record->patient_full_name} is waiting.",
        'queue_id' => $queue->id,
    ]));
}
```

### 5.2 Available Doctors Logic

```php
public function getAvailableDoctors(): Collection
{
    $today = today();
    $dayOfWeek = $today->dayOfWeek;

    return User::role('doctor')
        ->whereHas('doctorSchedules', function ($q) use ($dayOfWeek, $today) {
            $q->where('consultation_type_id', $this->consultationTypeId)
              ->where(function ($sub) use ($dayOfWeek, $today) {
                  // Regular schedule for today's day
                  $sub->where(function ($r) use ($dayOfWeek) {
                      $r->where('schedule_type', 'regular')
                        ->where('day_of_week', $dayOfWeek)
                        ->where('is_available', true);
                  })
                  // Or exception allowing today
                  ->orWhere(function ($e) use ($today) {
                      $e->where('schedule_type', 'exception')
                        ->where('date', $today)
                        ->where('is_available', true);
                  });
              });
        })
        ->with('personalInformation')
        ->get();
}
```

---

## 6. Walk-in Registration

### 6.1 Direct Patient Registration

**Component:** `Nurse\WalkInRegistration`

For walk-in patients without prior appointment:

```php
public function registerWalkIn(): void
{
    // Create appointment record
    $appointment = Appointment::create([
        'user_id' => $this->userId,  // Account owner (may be parent)
        'consultation_type_id' => $this->consultationTypeId,

        // Patient info from form
        'patient_first_name' => $this->patientFirstName,
        'patient_middle_name' => $this->patientMiddleName,
        'patient_last_name' => $this->patientLastName,
        'patient_date_of_birth' => $this->patientDateOfBirth,
        'patient_gender' => $this->patientGender,
        'patient_phone' => $this->patientPhone,
        'patient_province' => $this->patientProvince,
        'patient_municipality' => $this->patientMunicipality,
        'patient_barangay' => $this->patientBarangay,
        'patient_street' => $this->patientStreet,

        'relationship_to_account' => $this->relationshipToAccount,
        'appointment_date' => today(),
        'appointment_time' => now()->format('H:i'),
        'chief_complaints' => $this->chiefComplaints,
        'status' => 'checked_in',      // Skip pending/approved
        'source' => 'walk-in',          // Mark as walk-in
        'approved_by' => auth()->id(),
        'approved_at' => now(),
        'checked_in_at' => now(),
    ]);

    // Create queue immediately
    $queue = Queue::create([
        'appointment_id' => $appointment->id,
        'user_id' => $appointment->user_id,
        'consultation_type_id' => $appointment->consultation_type_id,
        'queue_number' => $this->generateQueueNumber(),
        'queue_date' => today(),
        'priority' => $this->priority,
        'status' => 'waiting',
        'source' => 'walk-in',
    ]);

    broadcast(new QueueUpdated($queue));
}
```

---

## 7. Dashboard Alerts

### 7.1 Dashboard Stats

**Component:** `Nurse\Dashboard`

```php
public function stats(): array
{
    return [
        'pending_appointments' => Appointment::where('status', 'pending')
            ->whereDate('appointment_date', today())
            ->count(),

        'waiting_queue' => Queue::today()->waiting()->count(),
        'serving_queue' => Queue::today()->serving()->count(),
        'completed_today' => Queue::today()
            ->where('status', 'completed')
            ->count(),
    ];
}
```

### 7.2 Alert Conditions

```php
public function alerts(): array
{
    $alerts = [];

    // Long waiting time alert
    $longWait = Queue::today()
        ->where('status', 'waiting')
        ->where('created_at', '<', now()->subMinutes(30))
        ->count();

    if ($longWait > 0) {
        $alerts[] = [
            'type' => 'warning',
            'message' => "{$longWait} patients waiting over 30 minutes",
        ];
    }

    // Abnormal vitals alert
    $abnormalVitals = MedicalRecord::whereDate('visit_date', today())
        ->where('status', 'in_progress')
        ->where(function ($q) {
            $q->where('temperature', '>', 38.5)
              ->orWhereRaw("CAST(SUBSTRING_INDEX(blood_pressure, '/', 1) AS UNSIGNED) > 140");
        })
        ->count();

    if ($abnormalVitals > 0) {
        $alerts[] = [
            'type' => 'danger',
            'message' => "{$abnormalVitals} patients with abnormal vitals",
        ];
    }

    return $alerts;
}
```

---

## Key Relationships Used

```php
// Queue → Appointment
$queue->appointment             // BelongsTo

// Queue → Medical Record
$queue->medicalRecord           // HasOne

// Queue → Served By (Nurse)
$queue->servedBy                // BelongsTo User

// Appointment → User (Account Owner)
$appointment->user              // BelongsTo

// Consultation Type → Doctor Schedules
$consultationType->doctorSchedules  // HasMany

// Doctor Schedule → Doctor
$schedule->doctor               // BelongsTo User
```

---

## Form Field Mappings

### Walk-in Form → appointments + queues

| Form Field | Table.Column | Notes |
|------------|--------------|-------|
| Patient Type | appointments.relationship_to_account | self/dependent |
| First Name | appointments.patient_first_name | |
| Last Name | appointments.patient_last_name | |
| DOB | appointments.patient_date_of_birth | |
| Gender | appointments.patient_gender | |
| Phone | appointments.patient_phone | |
| Complaints | appointments.chief_complaints | |
| Priority | queues.priority | normal/urgent/emergency |

### Interview Form → medical_records

| Form Step | Columns Updated |
|-----------|-----------------|
| Step 1 | patient_marital_status, patient_occupation, patient_religion, companion_* |
| Step 2 | patient_blood_type, patient_allergies, patient_chronic_conditions, emergency_contact_* |
| Step 3 | chief_complaints_updated |
| Step 4 | temperature, blood_pressure, cardiac_rate, respiratory_rate, weight, height, etc. |
| Step 5 | service_type, service_category, ob_type |
