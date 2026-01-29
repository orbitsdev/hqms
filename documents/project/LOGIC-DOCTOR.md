# Doctor Module Logic

## Overview

The Doctor Station enables physicians to:
- View patients forwarded from nurse triage
- Conduct examinations
- Record diagnoses and treatment plans
- Prescribe medications (external or hospital inventory)
- Recommend admissions
- Export medical records as PDF

---

## Tables Used

| Table | Purpose in Doctor Module |
|-------|--------------------------|
| `queues` | View forwarded patients |
| `medical_records` | Add diagnosis, plan, notes |
| `prescriptions` | Medication orders |
| `hospital_drugs` | Select from inventory |
| `admissions` | Create admission records |
| `doctor_schedules` | View own schedule |

---

## 1. Patient Queue

### 1.1 Forwarded Patients

**Component:** `Doctor\PatientQueue`

Doctors see only patients forwarded to them:

```php
public function waitingPatients(): Collection
{
    return Queue::query()
        ->today()
        ->where('doctor_id', auth()->id())  // Assigned to this doctor
        ->where('status', 'waiting')         // Not yet examined
        ->with([
            'medicalRecord',
            'consultationType',
            'appointment',
        ])
        ->orderByRaw("CASE priority
            WHEN 'emergency' THEN 1
            WHEN 'urgent' THEN 2
            WHEN 'normal' THEN 3
            ELSE 4 END")
        ->orderBy('queue_number')
        ->get();
}
```

### 1.2 Queue Information Displayed

```blade
@foreach($waitingPatients as $queue)
    <div>
        <span>{{ $queue->formatted_number }}</span>  {{-- PED-001 --}}
        <span>{{ $queue->medicalRecord->patient_full_name }}</span>
        <span>{{ $queue->medicalRecord->patient_age }} y/o</span>
        <span>{{ ucfirst($queue->priority) }}</span>

        {{-- Vital Signs Summary --}}
        <span>T: {{ $queue->medicalRecord->temperature }}°C</span>
        <span>BP: {{ $queue->medicalRecord->blood_pressure }}</span>

        {{-- Chief Complaints --}}
        <p>{{ $queue->medicalRecord->effective_chief_complaints }}</p>

        <button wire:click="startExamination({{ $queue->id }})">
            Examine
        </button>
    </div>
@endforeach
```

---

## 2. Medical Examination

### 2.1 Starting Examination

**Component:** `Doctor\Examination`

```php
public function mount(Queue $queue): void
{
    // Security: Ensure queue is assigned to this doctor
    if ($queue->doctor_id !== auth()->id()) {
        abort(403);
    }

    $this->queue = $queue;
    $this->medicalRecord = $queue->medicalRecord;

    // Mark examination started
    $this->medicalRecord->update([
        'examined_at' => now(),
        'examination_time' => now()->hour < 12 ? 'am' : 'pm',
    ]);

    // Load existing prescriptions
    $this->loadPrescriptions();
}
```

### 2.2 Examination Form Fields

**Migration:** `2026_01_19_090004_create_medical_records_table.php` (Doctor fields)

```php
// Doctor fills these columns during examination:

// Clinical findings
'pertinent_hpi_pe' => text,  // History of Present Illness / Physical Exam
    // Example: "Patient presents with 3-day history of fever, highest
    // recorded at 39°C. Associated symptoms include productive cough
    // with yellowish phlegm, rhinorrhea, and mild throat pain.
    // PE: Pharyngeal erythema noted. Chest clear on auscultation."

// Assessment
'diagnosis' => text,
    // Example: "Upper Respiratory Tract Infection"

// Plan
'plan' => text,
    // Example: "1. Continue antipyretics for fever
    // 2. Increase oral fluid intake
    // 3. Return if symptoms worsen or persist beyond 5 days"

// Procedures (if any)
'procedures_done' => text,
    // Example: "Throat swab taken for culture"

// Prescription notes (general notes about Rx)
'prescription_notes' => text,
    // Example: "Complete full course of antibiotics"
```

### 2.3 Form Binding

```php
// Livewire component
public string $pertinentHpiPe = '';
public string $diagnosis = '';
public string $plan = '';
public string $proceduresDone = '';
public string $prescriptionNotes = '';

public function mount(Queue $queue): void
{
    // ... security checks ...

    // Pre-fill from record
    $this->pertinentHpiPe = $this->medicalRecord->pertinent_hpi_pe ?? '';
    $this->diagnosis = $this->medicalRecord->diagnosis ?? '';
    $this->plan = $this->medicalRecord->plan ?? '';
}

public function saveExamination(): void
{
    $this->medicalRecord->update([
        'pertinent_hpi_pe' => $this->pertinentHpiPe,
        'diagnosis' => $this->diagnosis,
        'plan' => $this->plan,
        'procedures_done' => $this->proceduresDone,
        'prescription_notes' => $this->prescriptionNotes,
    ]);
}
```

---

## 3. Prescriptions

### 3.1 Prescription Structure

**Migration:** `2026_01_19_090006_create_prescriptions_table.php`

```php
Schema::create('prescriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('medical_record_id')->constrained()->onDelete('cascade');
    $table->foreignId('hospital_drug_id')->nullable()->constrained();

    $table->string('medication_name');        // Drug name
    $table->string('dosage')->nullable();     // e.g., "500mg"
    $table->string('frequency')->nullable();  // e.g., "3x daily"
    $table->string('duration')->nullable();   // e.g., "7 days"
    $table->integer('quantity')->nullable();  // e.g., 21 tablets
    $table->string('unit', 50)->nullable();   // e.g., "tablets", "capsules"
    $table->text('instructions')->nullable(); // Additional notes

    $table->boolean('is_hospital_drug')->default(false);
    $table->timestamps();
});
```

### 3.2 Hospital Drug Selection

**Migration:** `2026_01_19_090005_create_hospital_drugs_table.php`

```php
Schema::create('hospital_drugs', function (Blueprint $table) {
    $table->id();
    $table->string('name');               // Brand/trade name
    $table->string('generic_name');       // Generic name
    $table->string('category', 100);      // e.g., "Antibiotic", "Analgesic"
    $table->string('unit', 50);           // e.g., "tablet", "ml"
    $table->decimal('unit_price', 10, 2); // Price per unit
    $table->integer('stock_quantity');    // Current inventory
    $table->integer('reorder_level');     // Alert threshold
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### 3.3 Adding Prescription (External)

For medications patient will buy outside:

```php
public function addPrescription(): void
{
    $this->validate([
        'medicationName' => 'required|string|max:255',
        'dosage' => 'nullable|string|max:100',
        'frequency' => 'nullable|string|max:100',
        'duration' => 'nullable|string|max:100',
        'quantity' => 'nullable|integer|min:1',
        'unit' => 'nullable|string|max:50',
        'instructions' => 'nullable|string',
    ]);

    Prescription::create([
        'medical_record_id' => $this->medicalRecord->id,
        'hospital_drug_id' => null,  // External drug
        'medication_name' => $this->medicationName,
        'dosage' => $this->dosage,
        'frequency' => $this->frequency,
        'duration' => $this->duration,
        'quantity' => $this->quantity,
        'unit' => $this->unit,
        'instructions' => $this->instructions,
        'is_hospital_drug' => false,
    ]);

    $this->resetPrescriptionForm();
}
```

### 3.4 Adding Prescription (Hospital Drug)

For medications from hospital inventory (will appear in bill):

```php
public function addHospitalDrug(int $drugId): void
{
    $drug = HospitalDrug::findOrFail($drugId);

    $this->validate([
        'quantity' => 'required|integer|min:1|max:' . $drug->stock_quantity,
    ]);

    Prescription::create([
        'medical_record_id' => $this->medicalRecord->id,
        'hospital_drug_id' => $drug->id,
        'medication_name' => $drug->name,
        'dosage' => $this->dosage,
        'frequency' => $this->frequency,
        'duration' => $this->duration,
        'quantity' => $this->quantity,
        'unit' => $drug->unit,
        'instructions' => $this->instructions,
        'is_hospital_drug' => true,  // Will appear in bill
    ]);

    // Note: Stock is NOT deducted yet (deducted when paid)
}
```

### 3.5 Prescription Display

```blade
<h3>Prescriptions</h3>

@foreach($medicalRecord->prescriptions as $rx)
    <div class="{{ $rx->is_hospital_drug ? 'bg-green-50' : '' }}">
        <strong>{{ $rx->medication_name }}</strong>
        @if($rx->is_hospital_drug)
            <span class="badge">Hospital</span>
        @endif

        <p>
            {{ $rx->dosage }}
            {{ $rx->frequency }}
            for {{ $rx->duration }}
        </p>

        <p>Qty: {{ $rx->quantity }} {{ $rx->unit }}</p>

        @if($rx->instructions)
            <p class="italic">{{ $rx->instructions }}</p>
        @endif

        <button wire:click="removePrescription({{ $rx->id }})">
            Remove
        </button>
    </div>
@endforeach
```

---

## 4. Discount Suggestions

### 4.1 Doctor's Discount Recommendation

Doctors can suggest discounts for billing:

```php
// medical_records columns
'suggested_discount_type' => enum [
    'none',      // No discount
    'family',    // Family/regular patient discount
    'senior',    // Senior citizen (20%)
    'pwd',       // PWD (20%)
    'employee',  // Hospital employee
    'other',     // Custom discount
],

'suggested_discount_reason' => text,
    // Example: "Long-time patient, financial difficulty"
```

### 4.2 Setting Discount Suggestion

```php
public function suggestDiscount(): void
{
    $this->medicalRecord->update([
        'suggested_discount_type' => $this->suggestedDiscountType,
        'suggested_discount_reason' => $this->suggestedDiscountReason,
    ]);
}
```

**Note:** This is only a recommendation. Cashier decides the actual discount.

---

## 5. Hospital Admission

### 5.1 Admission Creation

**Migration:** `2026_01_19_090010_create_admissions_table.php`

```php
Schema::create('admissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('medical_record_id')->constrained();
    $table->foreignId('user_id')->constrained();  // Patient account
    $table->string('admission_number', 20)->unique();

    $table->foreignId('admitted_by')->constrained('users');  // Doctor
    $table->string('room_number', 20)->nullable();
    $table->string('bed_number', 20)->nullable();

    $table->timestamp('admitted_at');
    $table->timestamp('discharged_at')->nullable();

    $table->enum('status', ['admitted', 'discharged', 'transferred']);
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### 5.2 Creating Admission

```php
public function createAdmission(): void
{
    $this->validate([
        'roomNumber' => 'required|string|max:20',
        'bedNumber' => 'required|string|max:20',
        'admissionNotes' => 'nullable|string',
    ]);

    // Generate admission number (ADM-2026-00001)
    $admissionNumber = $this->generateAdmissionNumber();

    Admission::create([
        'medical_record_id' => $this->medicalRecord->id,
        'user_id' => $this->medicalRecord->user_id,
        'admission_number' => $admissionNumber,
        'admitted_by' => auth()->id(),
        'room_number' => $this->roomNumber,
        'bed_number' => $this->bedNumber,
        'admitted_at' => now(),
        'status' => 'admitted',
        'notes' => $this->admissionNotes,
    ]);

    // Update medical record status
    $this->medicalRecord->update([
        'status' => 'for_admission',
        'service_type' => 'admission',
    ]);
}
```

---

## 6. Complete Examination

### 6.1 Finishing Examination

```php
public function completeExamination(): void
{
    $this->validate([
        'diagnosis' => 'required|string|min:3',
        'plan' => 'required|string|min:3',
    ]);

    // Update medical record
    $this->medicalRecord->update([
        'pertinent_hpi_pe' => $this->pertinentHpiPe,
        'diagnosis' => $this->diagnosis,
        'plan' => $this->plan,
        'procedures_done' => $this->proceduresDone,
        'prescription_notes' => $this->prescriptionNotes,
        'suggested_discount_type' => $this->suggestedDiscountType,
        'suggested_discount_reason' => $this->suggestedDiscountReason,
        'examination_ended_at' => now(),
        'status' => 'for_billing',  // Ready for cashier
    ]);

    // Update queue status
    $this->queue->update([
        'status' => 'completed',
    ]);

    broadcast(new QueueUpdated($this->queue));

    // Notify cashier
    User::role('cashier')->each(function ($cashier) {
        $cashier->notify(new GenericNotification([
            'type' => 'billing.ready',
            'title' => 'Patient Ready for Billing',
            'message' => "{$this->medicalRecord->patient_full_name} - {$this->medicalRecord->record_number}",
            'medical_record_id' => $this->medicalRecord->id,
        ]));
    });

    Toaster::success('Examination completed.');
    $this->redirect(route('doctor.queue'), navigate: true);
}
```

---

## 7. Patient History Lookup

### 7.1 Searching Patient History

**Component:** `Doctor\PatientHistory`

```php
public function searchHistory(): void
{
    $this->records = MedicalRecord::query()
        ->when($this->searchFirstName, fn ($q) =>
            $q->where('patient_first_name', 'like', "%{$this->searchFirstName}%")
        )
        ->when($this->searchLastName, fn ($q) =>
            $q->where('patient_last_name', 'like', "%{$this->searchLastName}%")
        )
        ->when($this->searchDob, fn ($q) =>
            $q->whereDate('patient_date_of_birth', $this->searchDob)
        )
        ->whereIn('status', ['completed', 'for_billing'])
        ->with(['consultationType', 'doctor.personalInformation'])
        ->orderByDesc('visit_date')
        ->limit(50)
        ->get();
}
```

### 7.2 During Examination - Quick History

```php
// In Examination component
public function patientHistory(): Collection
{
    return MedicalRecord::query()
        ->where('patient_first_name', $this->medicalRecord->patient_first_name)
        ->where('patient_last_name', $this->medicalRecord->patient_last_name)
        ->where('patient_date_of_birth', $this->medicalRecord->patient_date_of_birth)
        ->where('id', '!=', $this->medicalRecord->id)  // Exclude current
        ->whereIn('status', ['completed', 'for_billing'])
        ->with(['consultationType', 'prescriptions'])
        ->orderByDesc('visit_date')
        ->limit(10)
        ->get();
}
```

---

## 8. PDF Export

### 8.1 Medical Record PDF

Using `spatie/laravel-pdf`:

```php
public function downloadPdf(): \Symfony\Component\HttpFoundation\StreamedResponse
{
    $record = $this->medicalRecord->load([
        'consultationType',
        'doctor.personalInformation',
        'nurse.personalInformation',
        'prescriptions.hospitalDrug',
    ]);

    return Pdf::view('pdf.medical-record', [
        'record' => $record,
    ])
        ->format('a4')
        ->name("medical-record-{$record->record_number}.pdf")
        ->download();
}
```

### 8.2 PDF Template

```blade
{{-- resources/views/pdf/medical-record.blade.php --}}
<html>
<head>
    <title>Medical Record - {{ $record->record_number }}</title>
    <style>
        /* PDF styling */
    </style>
</head>
<body>
    <header>
        <h1>Guardiano Maternity and Children Clinic</h1>
        <h2>Medical Record</h2>
    </header>

    <section>
        <h3>Patient Information</h3>
        <p>Name: {{ $record->patient_full_name }}</p>
        <p>Age/Sex: {{ $record->patient_age }} / {{ ucfirst($record->patient_gender) }}</p>
        <p>Date of Visit: {{ $record->visit_date->format('F d, Y') }}</p>
        <p>Record #: {{ $record->record_number }}</p>
    </section>

    <section>
        <h3>Vital Signs</h3>
        <p>Temperature: {{ $record->temperature }}°C</p>
        <p>Blood Pressure: {{ $record->blood_pressure }}</p>
        <p>Heart Rate: {{ $record->cardiac_rate }} bpm</p>
    </section>

    <section>
        <h3>Assessment</h3>
        <p><strong>Chief Complaints:</strong></p>
        <p>{{ $record->effective_chief_complaints }}</p>

        <p><strong>Clinical Findings:</strong></p>
        <p>{{ $record->pertinent_hpi_pe }}</p>

        <p><strong>Diagnosis:</strong></p>
        <p>{{ $record->diagnosis }}</p>

        <p><strong>Plan:</strong></p>
        <p>{{ $record->plan }}</p>
    </section>

    <section>
        <h3>Prescriptions</h3>
        @foreach($record->prescriptions as $rx)
            <p>
                {{ $rx->medication_name }}
                {{ $rx->dosage }}
                {{ $rx->frequency }}
                x {{ $rx->duration }}
                (Qty: {{ $rx->quantity }} {{ $rx->unit }})
            </p>
        @endforeach
    </section>

    <footer>
        <p>Attending Physician: Dr. {{ $record->doctor->personalInformation->full_name }}</p>
        <p>Date: {{ now()->format('F d, Y') }}</p>
    </footer>
</body>
</html>
```

---

## 9. Doctor Schedule (Self-View)

### 9.1 Viewing Own Schedule

**Component:** `Doctor\MySchedule`

```php
public function schedules(): Collection
{
    return DoctorSchedule::query()
        ->where('user_id', auth()->id())
        ->with('consultationType')
        ->orderBy('schedule_type')
        ->orderBy('day_of_week')
        ->orderBy('date')
        ->get();
}
```

### 9.2 Schedule Display

```blade
<h3>Regular Schedule</h3>
@foreach($schedules->where('schedule_type', 'regular') as $schedule)
    <p>
        {{ ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$schedule->day_of_week] }}:
        {{ $schedule->start_time }} - {{ $schedule->end_time }}
        ({{ $schedule->consultationType->name }})
    </p>
@endforeach

<h3>Exceptions</h3>
@foreach($schedules->where('schedule_type', 'exception') as $schedule)
    <p>
        {{ $schedule->date->format('M d, Y') }}:
        @if($schedule->is_available)
            {{ $schedule->start_time }} - {{ $schedule->end_time }}
        @else
            <span class="text-red-500">Not Available</span>
        @endif
    </p>
@endforeach
```

---

## Key Relationships Used

```php
// Queue → Medical Record
$queue->medicalRecord           // HasOne

// Medical Record → Prescriptions
$medicalRecord->prescriptions   // HasMany

// Prescription → Hospital Drug
$prescription->hospitalDrug     // BelongsTo (nullable)

// Medical Record → Admission
$medicalRecord->admission       // HasOne

// Doctor → Doctor Schedules
$doctor->doctorSchedules        // HasMany
```

---

## Form Field Mappings

### Examination Form → medical_records

| Form Field | Column | Validation |
|------------|--------|------------|
| HPI/PE | pertinent_hpi_pe | nullable, string |
| Diagnosis | diagnosis | required, string, min:3 |
| Plan | plan | required, string, min:3 |
| Procedures | procedures_done | nullable, string |
| Rx Notes | prescription_notes | nullable, string |
| Discount Type | suggested_discount_type | nullable, enum |
| Discount Reason | suggested_discount_reason | nullable, string |

### Prescription Form → prescriptions

| Form Field | Column | Notes |
|------------|--------|-------|
| Drug Name | medication_name | Required |
| Dosage | dosage | e.g., "500mg" |
| Frequency | frequency | e.g., "3x daily" |
| Duration | duration | e.g., "7 days" |
| Quantity | quantity | Integer |
| Unit | unit | e.g., "tablets" |
| Instructions | instructions | Additional notes |
| Hospital Drug | hospital_drug_id | If from inventory |
| Is Hospital | is_hospital_drug | Boolean |

### Admission Form → admissions

| Form Field | Column | Notes |
|------------|--------|-------|
| Room | room_number | Required |
| Bed | bed_number | Required |
| Notes | notes | Optional |
