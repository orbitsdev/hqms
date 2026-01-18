# Database Schema Design - FINAL
## Hospital Queue Management System
### Guardiano Maternity & Children Clinic and Hospital

**Database Type:** MySQL / PostgreSQL  
**ORM:** Laravel Eloquent  
**Auth Package:** Laravel Sanctum (API)  
**Permission Package:** Spatie Laravel Permission  
**Real-time:** Laravel Reverb

---

## Design Principles

- ✅ **Simple & focused** - Essential tables only
- ✅ **Flexible** - Consultation types can be added/modified
- ✅ **Unified data** - Walk-ins and online use same structure
- ✅ **Laravel migrations** - No complex SQL
- ✅ **Soft deletes** for sensitive data
- ✅ **Timestamps** on all tables
- ✅ **Real hospital services** - Actual pricing from hospital

---

## Core Tables Summary (16 + Spatie)

**User Management:**
1. `users` - Everyone (patients + staff with medical history)

**Consultation Management:**
2. `consultation_types` - Flexible types (OB/PEDIA/GENERAL + future)
3. `doctor_consultation_types` - Doctor specializations (pivot)

**Appointment & Queue:**
4. `doctor_schedules` - Doctor availability
5. `appointments` - Online + Walk-in bookings
6. `queues` - Queue management (O-1, P-1, G-1)

**Medical Records:**
7. `medical_records` - All-in-one patient records
8. `prescriptions` - Medications (separate for multiple drugs)

**Billing & Services:**
9. `services` - Hospital services with pricing
10. `billing_transactions` - Main billing
11. `billing_items` - Itemized charges
12. `hospital_drugs` - Available medications

**Other:**
13. `admissions` - Track admissions (billing external)
14. `system_settings` - Configuration
15. `queue_displays` - Monitor management
16. `notifications` - Laravel notifications
17. **Spatie tables** - roles, permissions, pivots

---

## 1. users (Everyone - Patients + Staff)

**Purpose:** Single table for all system users with medical history

**Laravel Migration:**
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    
    // Authentication
    $table->string('email')->unique();
    $table->string('phone', 20)->unique();
    $table->string('password');
    
    // Personal Information
    $table->string('first_name');
    $table->string('middle_name')->nullable();
    $table->string('last_name');
    
    // Demographics
    $table->date('date_of_birth')->nullable();
    $table->enum('gender', ['male', 'female'])->nullable();
    $table->enum('marital_status', ['child', 'single', 'married', 'widow'])->nullable();
    
    // Address
    $table->string('province')->nullable();
    $table->string('municipality')->nullable();
    $table->string('barangay')->nullable();
    $table->text('street')->nullable();
    
    // Medical History (NEW)
    $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
    $table->text('allergies')->nullable(); // Known allergies
    $table->text('chronic_conditions')->nullable(); // Diabetes, Hypertension, etc.
    
    // Additional
    $table->string('occupation')->nullable();
    $table->string('emergency_contact_name')->nullable();
    $table->string('emergency_contact_phone', 20)->nullable();
    
    // System
    $table->boolean('is_active')->default(true);
    $table->timestamp('email_verified_at')->nullable();
    $table->timestamp('phone_verified_at')->nullable();
    $table->rememberToken();
    
    $table->timestamps();
    $table->softDeletes();
});
```

**Model Relationships:**
```php
class User extends Authenticatable
{
    use HasRoles, Notifiable, SoftDeletes;
    
    // Consultation types for doctors
    public function consultationTypes() {
        return $this->belongsToMany(ConsultationType::class, 'doctor_consultation_types');
    }
    
    // Medical records
    public function medicalRecords() {
        return $this->hasMany(MedicalRecord::class);
    }
    
    // Appointments
    public function appointments() {
        return $this->hasMany(Appointment::class, 'user_id');
    }
    
    public function doctorAppointments() {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }
    
    // Schedules
    public function doctorSchedules() {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id');
    }
    
    // Queues
    public function queues() {
        return $this->hasMany(Queue::class, 'user_id');
    }
    
    // Billing
    public function billingTransactions() {
        return $this->hasMany(BillingTransaction::class, 'user_id');
    }
    
    // Admissions
    public function admissions() {
        return $this->hasMany(Admission::class, 'user_id');
    }
    
    // Helper methods
    public function isPatient() {
        return $this->hasRole('patient');
    }
    
    public function isDoctor() {
        return $this->hasRole('doctor');
    }
    
    public function isNurse() {
        return $this->hasRole('nurse');
    }
}
```

**Spatie Roles:**
- `patient` - Mobile app users
- `nurse` - Queue management, vital signs
- `doctor` - Diagnosis, prescriptions, discount approval
- `cashier` - Billing, payment processing
- `admin` - Full system access

---

## 2. consultation_types (Flexible Consultation Types)

**Purpose:** Manage consultation types dynamically (OB, PEDIA, GENERAL + future additions)

**Laravel Migration:**
```php
Schema::create('consultation_types', function (Blueprint $table) {
    $table->id();
    
    // Identification
    $table->string('code', 10)->unique(); // 'ob', 'pedia', 'general'
    $table->string('name'); // 'Obstetrics', 'Pediatrics', 'General Medicine'
    $table->string('short_name', 5); // 'O', 'P', 'G' (for queue display)
    $table->text('description')->nullable();
    
    // Operating Hours
    $table->time('start_time'); // 08:00
    $table->time('end_time'); // 17:00
    
    // Queue Settings
    $table->integer('avg_duration')->default(30); // Average minutes per patient
    $table->integer('max_daily_patients')->default(50); // Max appointments per day
    
    // Display
    $table->string('color_code', 7)->nullable(); // #FF5733 for UI
    $table->integer('display_order')->default(0); // Sort order in lists
    
    // Status
    $table->boolean('is_active')->default(true);
    
    $table->timestamps();
});
```

**Seeder:**
```php
class ConsultationTypeSeeder extends Seeder
{
    public function run()
    {
        ConsultationType::create([
            'code' => 'ob',
            'name' => 'Obstetrics',
            'short_name' => 'O',
            'description' => 'Pregnancy and maternal care',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'avg_duration' => 30,
            'max_daily_patients' => 40,
            'color_code' => '#FF6B9D',
            'display_order' => 1,
        ]);
        
        ConsultationType::create([
            'code' => 'pedia',
            'name' => 'Pediatrics',
            'short_name' => 'P',
            'description' => 'Children healthcare',
            'start_time' => '08:00',
            'end_time' => '15:00',
            'avg_duration' => 25,
            'max_daily_patients' => 35,
            'color_code' => '#4ECDC4',
            'display_order' => 2,
        ]);
        
        ConsultationType::create([
            'code' => 'general',
            'name' => 'General Medicine',
            'short_name' => 'G',
            'description' => 'General medical consultation',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'avg_duration' => 20,
            'max_daily_patients' => 50,
            'color_code' => '#95E1D3',
            'display_order' => 3,
        ]);
    }
}
```

**Model:**
```php
class ConsultationType extends Model
{
    public function doctors() {
        return $this->belongsToMany(User::class, 'doctor_consultation_types');
    }
    
    public function appointments() {
        return $this->hasMany(Appointment::class);
    }
    
    public function queues() {
        return $this->hasMany(Queue::class);
    }
    
    public function queueDisplays() {
        return $this->hasMany(QueueDisplay::class);
    }
    
    // Get current queue count for today
    public function getTodayQueueCount() {
        return $this->queues()
            ->where('queue_date', today())
            ->count();
    }
    
    // Check if accepting new appointments
    public function isAcceptingAppointments($date) {
        $count = $this->appointments()
            ->where('appointment_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->count();
            
        return $count < $this->max_daily_patients;
    }
}
```

---

## 3. doctor_consultation_types (Doctor Specializations)

**Purpose:** Many-to-many relationship between doctors and consultation types

**Laravel Migration:**
```php
Schema::create('doctor_consultation_types', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');
    
    $table->primary(['user_id', 'consultation_type_id'], 'doctor_consult_type_primary');
    $table->timestamps();
});
```

**Usage:**
```php
// Assign doctor to consultation types
$doctor = User::find(5);
$doctor->consultationTypes()->attach([1, 2]); // OB and PEDIA

// Get all OB doctors
$obDoctors = User::role('doctor')
    ->whereHas('consultationTypes', function($q) {
        $q->where('code', 'ob');
    })
    ->get();

// Check if doctor handles specific type
if ($doctor->consultationTypes->contains('code', 'ob')) {
    // Doctor handles OB patients
}
```

---

## 4. doctor_schedules (Doctor Availability)

**Purpose:** Track when doctors are available

**Laravel Migration:**
```php
Schema::create('doctor_schedules', function (Blueprint $table) {
    $table->id();
    
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Doctor
    $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');
    
    // Schedule Type
    $table->enum('schedule_type', ['regular', 'specific_date', 'leave']);
    
    // For Regular Schedule (weekly)
    $table->tinyInteger('day_of_week')->nullable(); // 0=Sun, 1=Mon, ... 6=Sat
    
    // For Specific Date or Leave
    $table->date('date')->nullable();
    
    // Time Slots
    $table->time('start_time')->nullable();
    $table->time('end_time')->nullable();
    
    // Capacity
    $table->integer('max_patients')->default(20);
    
    // Status
    $table->boolean('is_available')->default(true);
    $table->text('notes')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
});
```

**Model:**
```php
class DoctorSchedule extends Model
{
    public function doctor() {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function consultationType() {
        return $this->belongsTo(ConsultationType::class);
    }
}
```

---

## 5. appointments (Online + Walk-in Bookings)

**Purpose:** Patient appointment requests

**Laravel Migration:**
```php
Schema::create('appointments', function (Blueprint $table) {
    $table->id();
    
    // Relations
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Patient
    $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');
    $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');
    
    // Appointment Details
    $table->date('appointment_date');
    $table->time('appointment_time')->nullable();
    
    // Initial Symptoms
    $table->text('chief_complaints')->nullable();
    
    // Status Flow
    $table->enum('status', [
        'pending',      // Submitted, waiting approval
        'approved',     // Nurse approved, queue assigned
        'checked_in',   // Patient arrived
        'in_progress',  // Currently serving
        'completed',    // Finished
        'cancelled',    // Cancelled by patient/staff
        'no_show'       // Didn't show up
    ])->default('pending');
    
    // Tracking
    $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('checked_in_at')->nullable();
    
    // Decline Handling
    $table->text('decline_reason')->nullable();
    $table->date('suggested_date')->nullable(); // Nurse suggests alternative date
    
    // Source
    $table->enum('source', ['online', 'walk-in'])->default('online');
    
    // Notes
    $table->text('notes')->nullable();
    $table->text('cancellation_reason')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
});
```

**Model:**
```php
class Appointment extends Model
{
    public function user() {
        return $this->belongsTo(User::class); // Patient
    }
    
    public function consultationType() {
        return $this->belongsTo(ConsultationType::class);
    }
    
    public function doctor() {
        return $this->belongsTo(User::class, 'doctor_id');
    }
    
    public function approvedBy() {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function queue() {
        return $this->hasOne(Queue::class);
    }
    
    public function medicalRecord() {
        return $this->hasOne(MedicalRecord::class);
    }
    
    // Scopes
    public function scopeToday($query) {
        return $query->where('appointment_date', today());
    }
    
    public function scopePending($query) {
        return $query->where('status', 'pending');
    }
    
    public function scopeApproved($query) {
        return $query->where('status', 'approved');
    }
}
```

**Status Flow:**
```
pending → approved → checked_in → in_progress → completed
          ↓
       declined (with reason + suggested date)
          ↓
       cancelled / no_show
```

---

## 6. queues (Daily Queue Management)

**Purpose:** Real-time queue (online + walk-in combined)

**Laravel Migration:**
```php
Schema::create('queues', function (Blueprint $table) {
    $table->id();
    
    // Relations
    $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Patient
    $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');
    $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');
    
    // Queue Information
    $table->integer('queue_number'); // Simple: 1, 2, 3, 4, 5...
    $table->date('queue_date');
    
    // Timing
    $table->time('estimated_time')->nullable(); // Auto-calculated
    
    // Priority
    $table->enum('priority', ['normal', 'urgent', 'emergency'])->default('normal');
    
    // Status
    $table->enum('status', [
        'waiting',   // Waiting to be called
        'called',    // Called/notified
        'serving',   // Currently being served
        'completed', // Service completed
        'skipped',   // Not present when called
        'cancelled'  // Removed from queue
    ])->default('waiting');
    
    // Tracking
    $table->timestamp('called_at')->nullable();
    $table->timestamp('serving_started_at')->nullable();
    $table->timestamp('serving_ended_at')->nullable();
    $table->foreignId('served_by')->nullable()->constrained('users')->onDelete('set null');
    
    // Source
    $table->enum('source', ['online', 'walk-in'])->default('online');
    
    $table->text('notes')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
    
    // Each type has separate counter - resets daily
    $table->unique(['queue_number', 'queue_date', 'consultation_type_id'], 'unique_queue_per_type_date');
});
```

**Model:**
```php
class Queue extends Model
{
    protected $casts = [
        'queue_date' => 'date',
        'estimated_time' => 'datetime',
        'called_at' => 'datetime',
        'serving_started_at' => 'datetime',
        'serving_ended_at' => 'datetime',
    ];
    
    public function appointment() {
        return $this->belongsTo(Appointment::class);
    }
    
    public function user() {
        return $this->belongsTo(User::class); // Patient
    }
    
    public function consultationType() {
        return $this->belongsTo(ConsultationType::class);
    }
    
    public function doctor() {
        return $this->belongsTo(User::class, 'doctor_id');
    }
    
    public function servedBy() {
        return $this->belongsTo(User::class, 'served_by');
    }
    
    public function medicalRecord() {
        return $this->hasOne(MedicalRecord::class);
    }
    
    // Get formatted queue number with prefix
    public function getFormattedNumberAttribute() {
        return $this->consultationType->short_name . '-' . $this->queue_number;
    }
    
    // Calculate wait time
    public function getWaitTimeAttribute() {
        if (!$this->serving_started_at) return null;
        
        $start = $this->created_at;
        $end = $this->serving_started_at;
        
        return $start->diffInMinutes($end);
    }
    
    // Calculate service time
    public function getServiceTimeAttribute() {
        if (!$this->serving_started_at || !$this->serving_ended_at) return null;
        
        return $this->serving_started_at->diffInMinutes($this->serving_ended_at);
    }
    
    // Scopes
    public function scopeToday($query) {
        return $query->where('queue_date', today());
    }
    
    public function scopeWaiting($query) {
        return $query->where('status', 'waiting');
    }
    
    public function scopeServing($query) {
        return $query->where('status', 'serving');
    }
}
```

**Queue Number Format:**
- Display: `O-1, P-1, G-1` (short prefix)
- Database: Integer `1, 2, 3, 4...`
- Formatted via accessor

**Auto-Estimated Time Calculation:**
```php
// When queue is created
$queueCount = Queue::where('queue_date', $date)
    ->where('consultation_type_id', $typeId)
    ->where('status', 'waiting')
    ->count();

$consultationType = ConsultationType::find($typeId);
$startTime = Carbon::parse($consultationType->start_time);

$estimatedTime = $startTime->addMinutes($queueCount * $consultationType->avg_duration);

$queue->estimated_time = $estimatedTime;
```

---

## 7. medical_records (All-in-One Patient Records)

**Purpose:** Complete visit record including vitals, diagnosis, prescription notes

**Laravel Migration:**
```php
Schema::create('medical_records', function (Blueprint $table) {
    $table->id();
    
    // Relations
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Patient
    $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');
    $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('queue_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('nurse_id')->nullable()->constrained('users')->onDelete('set null');
    
    // Visit Information
    $table->date('visit_date');
    $table->enum('visit_type', ['new', 'old', 'revisit']);
    $table->enum('service_type', ['checkup', 'admission']);
    
    // Chief Complaints (Two fields as confirmed)
    $table->text('chief_complaints_initial')->nullable(); // From app/initial booking
    $table->text('chief_complaints_updated')->nullable(); // Nurse updates during interview
    
    // === VITAL SIGNS (Nurse Input) ===
    // Basic Vitals (All Types)
    $table->decimal('temperature', 4, 1)->nullable(); // °C
    $table->string('blood_pressure', 20)->nullable(); // "120/80"
    $table->integer('cardiac_rate')->nullable(); // bpm
    $table->integer('respiratory_rate')->nullable(); // cpm
    
    // PEDIA / GENERAL Specific
    $table->decimal('weight', 5, 2)->nullable(); // kg
    $table->decimal('height', 5, 2)->nullable(); // cm
    $table->decimal('head_circumference', 5, 2)->nullable(); // cm
    $table->decimal('chest_circumference', 5, 2)->nullable(); // cm
    
    // OB Specific
    $table->integer('fetal_heart_tone')->nullable(); // bpm
    $table->decimal('fundal_height', 5, 2)->nullable(); // cm
    $table->date('last_menstrual_period')->nullable(); // LMP
    
    // Vital Signs Timing
    $table->timestamp('vital_signs_recorded_at')->nullable();
    
    // === DIAGNOSIS (Doctor Input) ===
    $table->text('pertinent_hpi_pe')->nullable(); // History / Physical Exam
    $table->text('diagnosis')->nullable();
    $table->text('plan')->nullable(); // Treatment plan
    $table->text('procedures_done')->nullable();
    $table->text('prescription_notes')->nullable(); // Free-text prescription notes
    
    // Examination Timing
    $table->timestamp('examined_at')->nullable();
    $table->enum('examination_time', ['am', 'pm'])->nullable();
    
    // Status
    $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
    
    $table->text('notes')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
});
```

**Model:**
```php
class MedicalRecord extends Model
{
    public function user() {
        return $this->belongsTo(User::class); // Patient
    }
    
    public function consultationType() {
        return $this->belongsTo(ConsultationType::class);
    }
    
    public function doctor() {
        return $this->belongsTo(User::class, 'doctor_id');
    }
    
    public function nurse() {
        return $this->belongsTo(User::class, 'nurse_id');
    }
    
    public function appointment() {
        return $this->belongsTo(Appointment::class);
    }
    
    public function queue() {
        return $this->belongsTo(Queue::class);
    }
    
    public function prescriptions() {
        return $this->hasMany(Prescription::class);
    }
    
    public function billingTransaction() {
        return $this->hasOne(BillingTransaction::class);
    }
    
    // Get effective chief complaints (updated or initial)
    public function getEffectiveChiefComplaintsAttribute() {
        return $this->chief_complaints_updated ?? $this->chief_complaints_initial;
    }
}
```

---

## 8. prescriptions (Medications)

**Purpose:** Individual medications prescribed during visit

**Laravel Migration:**
```php
Schema::create('prescriptions', function (Blueprint $table) {
    $table->id();
    
    $table->foreignId('medical_record_id')->constrained()->onDelete('cascade');
    $table->foreignId('prescribed_by')->constrained('users')->onDelete('cascade'); // Doctor
    
    // Medication Details
    $table->string('medication_name');
    $table->string('dosage')->nullable(); // "500mg"
    $table->string('frequency')->nullable(); // "3 times a day"
    $table->string('duration')->nullable(); // "7 days"
    $table->text('instructions')->nullable(); // "Take after meals"
    $table->integer('quantity')->nullable();
    
    // Hospital Pharmacy
    $table->foreignId('hospital_drug_id')->nullable()->constrained()->onDelete('set null');
    $table->boolean('is_hospital_drug')->default(false);
    
    $table->timestamps();
});
```

**Model:**
```php
class Prescription extends Model
{
    public function medicalRecord() {
        return $this->belongsTo(MedicalRecord::class);
    }
    
    public function doctor() {
        return $this->belongsTo(User::class, 'prescribed_by');
    }
    
    public function hospitalDrug() {
        return $this->belongsTo(HospitalDrug::class);
    }
}
```

---

## 9. services (Hospital Services & Pricing)

**Purpose:** Hospital services with actual pricing

**Laravel Migration:**
```php
Schema::create('services', function (Blueprint $table) {
    $table->id();
    
    // Service Information
    $table->string('service_name'); // "Whole Abdomen Ultrasound"
    $table->enum('category', [
        'ultrasound',
        'consultation',
        'procedure',
        'laboratory',
        'other'
    ]);
    $table->text('description')->nullable();
    
    // Pricing
    $table->decimal('base_price', 10, 2); // 1200, 1500, 3500
    
    // Status
    $table->boolean('is_active')->default(true);
    $table->integer('display_order')->default(0);
    
    $table->timestamps();
});
```

**Seeder (Real Hospital Services):**
```php
class ServiceSeeder extends Seeder
{
    public function run()
    {
        // General Ultrasound Services
        $ultrasoundServices = [
            'Whole Abdomen' => 1500,
            'Adrenal Gland' => 1200,
            'Breast' => 1200,
            'Chest' => 1200,
            'Cranial' => 1200,
            'Extremities' => 1200,
            'Gallbladder/Liver' => 1200,
            'Hepatobiliary' => 1200,
            'Inguinal' => 1200,
            'Kidneys' => 1200,
            'Kidneys, Ureter, Bladder (KUB)' => 1200,
            'KUB/Liver' => 1200,
            'KUB/Pelvis' => 1200,
            'KUB/Prostate' => 1200,
            'Liver' => 1200,
            'Neck' => 1200,
            'Pelvis' => 1200,
            'Prostate' => 1200,
            'Scrotum' => 1200,
            'Thyroid' => 1200,
            'Upper Abdomen' => 1200,
            'Lower Abdomen' => 1200,
        ];
        
        foreach ($ultrasoundServices as $name => $price) {
            Service::create([
                'service_name' => $name,
                'category' => 'ultrasound',
                'base_price' => $price,
                'is_active' => true,
            ]);
        }
        
        // OB Consultation Services
        $obServices = [
            'Pelvic' => 1000,
            'TVS - OB' => 1500,
            'TVS - GYNE' => 1500,
            'TRS' => 1500,
            'BPS (Biophysical Profile Score)' => 1200,
            'CAS (Congenital Anomaly Scan)' => 3500,
            'Gyne Doppler' => 1800,
            '3D - 4D' => 3500,
            'SISH (Saline Infusion Sonohysterogram)' => 3000,
            'HSSG (Hysterosalpingosonogram)' => 3000,
            'Twins' => 2000,
        ];
        
        foreach ($obServices as $name => $price) {
            Service::create([
                'service_name' => $name,
                'category' => 'consultation',
                'base_price' => $price,
                'is_active' => true,
            ]);
        }
    }
}
```

**Model:**
```php
class Service extends Model
{
    public function billingItems() {
        return $this->hasMany(BillingItem::class);
    }
    
    // Get price with emergency fee if applicable
    public function getPriceWithEmergency($isEmergency = false) {
        $price = $this->base_price;
        
        if ($isEmergency) {
            $price += 500; // Additional ₱500 for emergency/after hours
        }
        
        return $price;
    }
}
```

---

## 10. billing_transactions (Main Billing)

**Purpose:** Financial transactions with discount support

**Laravel Migration:**
```php
Schema::create('billing_transactions', function (Blueprint $table) {
    $table->id();
    
    // Relations
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Patient
    $table->foreignId('medical_record_id')->nullable()->constrained()->onDelete('set null');
    
    // Transaction Details
    $table->string('transaction_number', 50)->unique(); // TXN-2026-000001
    $table->date('transaction_date');
    
    // Emergency/After Hours Charges
    $table->boolean('is_emergency')->default(false);
    $table->boolean('is_holiday')->default(false);
    $table->boolean('is_sunday')->default(false);
    $table->boolean('is_after_5pm')->default(false);
    $table->decimal('emergency_fee', 10, 2)->default(0); // +₱500
    
    // Amounts (calculated from billing_items)
    $table->decimal('subtotal', 10, 2);
    
    // Discount
    $table->enum('discount_type', [
        'none',
        'family',
        'senior',
        'pwd',
        'employee',
        'other'
    ])->default('none');
    $table->decimal('discount_amount', 10, 2)->default(0);
    $table->text('discount_reason')->nullable();
    
    $table->decimal('total_amount', 10, 2);
    
    // Payment
    $table->enum('payment_status', ['pending', 'partial', 'paid', 'cancelled'])->default('pending');
    $table->decimal('amount_paid', 10, 2)->default(0);
    $table->decimal('balance', 10, 2);
    $table->enum('payment_method', ['cash', 'gcash', 'card', 'bank_transfer', 'philhealth'])->nullable();
    
    // Timing
    $table->timestamp('received_in_billing_at')->nullable();
    $table->timestamp('ended_in_billing_at')->nullable();
    
    // Staff
    $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null'); // Cashier
    $table->foreignId('discount_approved_by')->nullable()->constrained('users')->onDelete('set null'); // Doctor
    
    $table->text('notes')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
});
```

**Model:**
```php
class BillingTransaction extends Model
{
    public function user() {
        return $this->belongsTo(User::class); // Patient
    }
    
    public function medicalRecord() {
        return $this->belongsTo(MedicalRecord::class);
    }
    
    public function billingItems() {
        return $this->hasMany(BillingItem::class);
    }
    
    public function processedBy() {
        return $this->belongsTo(User::class, 'processed_by');
    }
    
    public function discountApprovedBy() {
        return $this->belongsTo(User::class, 'discount_approved_by');
    }
    
    // Calculate totals
    public function calculateTotals() {
        $this->subtotal = $this->billingItems->sum('total_price');
        $this->total_amount = $this->subtotal + $this->emergency_fee - $this->discount_amount;
        $this->balance = $this->total_amount - $this->amount_paid;
        $this->save();
    }
    
    // Check if emergency charges apply
    public static function shouldApplyEmergencyFee($dateTime = null) {
        $dateTime = $dateTime ?? now();
        
        return $dateTime->hour >= 17 || // After 5pm
               $dateTime->isSunday() ||  // Sunday
               $dateTime->isHoliday();   // Holiday (need to implement holiday check)
    }
}
```

**Transaction Number:** `TXN-2026-000001` (Year + Sequential)

---

## 11. billing_items (Itemized Charges)

**Purpose:** Individual charges in a bill

**Laravel Migration:**
```php
Schema::create('billing_items', function (Blueprint $table) {
    $table->id();
    
    $table->foreignId('billing_transaction_id')->constrained()->onDelete('cascade');
    
    // Item Details
    $table->enum('item_type', [
        'professional_fee',
        'service',
        'drug',
        'procedure',
        'other'
    ]);
    $table->string('item_description');
    
    // Service Reference
    $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
    
    // Pricing
    $table->integer('quantity')->default(1);
    $table->decimal('unit_price', 10, 2);
    $table->decimal('total_price', 10, 2); // quantity * unit_price
    
    // For hospital drugs
    $table->foreignId('hospital_drug_id')->nullable()->constrained()->onDelete('set null');
    
    $table->timestamps();
});
```

**Model:**
```php
class BillingItem extends Model
{
    public function billingTransaction() {
        return $this->belongsTo(BillingTransaction::class);
    }
    
    public function service() {
        return $this->belongsTo(Service::class);
    }
    
    public function hospitalDrug() {
        return $this->belongsTo(HospitalDrug::class);
    }
    
    // Auto-calculate total price
    protected static function boot() {
        parent::boot();
        
        static::saving(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;
        });
    }
}
```

**Example Bill:**
```
Professional Fee         ₱500
Whole Abdomen Ultrasound ₱1,500
Amoxicillin 500mg x 21   ₱210
Emergency Fee (Sunday)   ₱500
----------------------------
Subtotal                ₱2,710
Discount (Family)        -₱200
----------------------------
TOTAL                   ₱2,510
```

---

## 12. hospital_drugs (Available Medications)

**Purpose:** Simple list of drugs (NO stock tracking)

**Laravel Migration:**
```php
Schema::create('hospital_drugs', function (Blueprint $table) {
    $table->id();
    
    // Drug Information
    $table->string('drug_name');
    $table->string('generic_name')->nullable();
    $table->text('description')->nullable();
    $table->decimal('unit_price', 10, 2);
    
    // Status
    $table->boolean('is_active')->default(true);
    
    $table->timestamps();
});
```

**Model:**
```php
class HospitalDrug extends Model
{
    public function prescriptions() {
        return $this->hasMany(Prescription::class);
    }
    
    public function billingItems() {
        return $this->hasMany(BillingItem::class);
    }
}
```

**Notes:**
- NO stock/quantity (existing system handles inventory)
- Just name + price for billing reference

---

## 13. admissions (Patient Admissions)

**Purpose:** Track admissions (billing handled by existing external system)

**Laravel Migration:**
```php
Schema::create('admissions', function (Blueprint $table) {
    $table->id();
    
    // Relations
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Patient
    $table->foreignId('medical_record_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('admitted_by')->constrained('users')->onDelete('cascade'); // Doctor
    
    // Admission Details
    $table->string('admission_number', 50)->unique(); // ADM-2026-001
    $table->dateTime('admission_date');
    $table->dateTime('discharge_date')->nullable();
    
    // Room/Bed
    $table->string('room_number', 50)->nullable();
    $table->string('bed_number', 50)->nullable();
    
    // Medical
    $table->text('reason_for_admission');
    $table->text('discharge_summary')->nullable();
    
    // Status
    $table->enum('status', ['active', 'discharged'])->default('active');
    
    $table->text('notes')->nullable();
    
    $table->timestamps();
});
```

**Model:**
```php
class Admission extends Model
{
    public function user() {
        return $this->belongsTo(User::class); // Patient
    }
    
    public function medicalRecord() {
        return $this->belongsTo(MedicalRecord::class);
    }
    
    public function admittedBy() {
        return $this->belongsTo(User::class, 'admitted_by');
    }
    
    // Calculate length of stay
    public function getLengthOfStayAttribute() {
        if (!$this->discharge_date) {
            return $this->admission_date->diffInDays(now());
        }
        
        return $this->admission_date->diffInDays($this->discharge_date);
    }
}
```

**Important Note:**
- This table only TRACKS admissions for reports
- Billing for admitted patients handled by existing external system
- No billing_transaction link for admitted patients

---

## 14. system_settings (Configuration)

**Purpose:** System-wide settings

**Laravel Migration:**
```php
Schema::create('system_settings', function (Blueprint $table) {
    $table->id();
    
    $table->string('setting_key', 100)->unique();
    $table->text('setting_value');
    $table->enum('setting_type', ['string', 'integer', 'boolean', 'json', 'time'])->default('string');
    $table->string('category', 50)->default('general'); // general, queue, billing, notification
    $table->text('description')->nullable();
    
    $table->timestamps();
});
```

**Seeder:**
```php
class SystemSettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            // Queue Settings
            ['key' => 'queue_reset_time', 'value' => '00:00', 'type' => 'time', 'category' => 'queue', 'description' => 'Time when daily queues reset'],
            ['key' => 'queue_nearby_threshold', 'value' => '3', 'type' => 'integer', 'category' => 'queue', 'description' => 'Notify when this many patients away'],
            
            // Billing Settings
            ['key' => 'emergency_fee_amount', 'value' => '500', 'type' => 'integer', 'category' => 'billing', 'description' => 'Emergency/after-hours additional fee'],
            ['key' => 'apply_emergency_fee_after', 'value' => '17:00', 'type' => 'time', 'category' => 'billing', 'description' => 'Apply emergency fee after this time'],
            
            // Notification Settings
            ['key' => 'appointment_reminder_1day', 'value' => 'true', 'type' => 'boolean', 'category' => 'notification', 'description' => 'Send reminder 1 day before'],
            ['key' => 'appointment_reminder_1hour', 'value' => 'true', 'type' => 'boolean', 'category' => 'notification', 'description' => 'Send reminder 1 hour before'],
            
            // Appointment Settings
            ['key' => 'max_advance_booking_days', 'value' => '30', 'type' => 'integer', 'category' => 'appointment', 'description' => 'How far in advance can book'],
            ['key' => 'allow_same_day_booking', 'value' => 'true', 'type' => 'boolean', 'category' => 'appointment', 'description' => 'Allow booking for same day'],
        ];
        
        foreach ($settings as $setting) {
            SystemSetting::create([
                'setting_key' => $setting['key'],
                'setting_value' => $setting['value'],
                'setting_type' => $setting['type'],
                'category' => $setting['category'],
                'description' => $setting['description'],
            ]);
        }
    }
}
```

**Model:**
```php
class SystemSetting extends Model
{
    // Helper method to get setting
    public static function get($key, $default = null) {
        $setting = self::where('setting_key', $key)->first();
        
        if (!$setting) return $default;
        
        return match($setting->setting_type) {
            'integer' => (int) $setting->setting_value,
            'boolean' => $setting->setting_value === 'true',
            'json' => json_decode($setting->setting_value, true),
            default => $setting->setting_value,
        };
    }
    
    // Helper method to set setting
    public static function set($key, $value) {
        $setting = self::where('setting_key', $key)->first();
        
        if ($setting) {
            $setting->setting_value = is_array($value) ? json_encode($value) : $value;
            $setting->save();
        }
        
        return $setting;
    }
}
```

**Usage:**
```php
// Get setting
$queueResetTime = SystemSetting::get('queue_reset_time'); // "00:00"
$emergencyFee = SystemSetting::get('emergency_fee_amount'); // 500

// Set setting
SystemSetting::set('emergency_fee_amount', 600);
```

---

## 15. queue_displays (Monitor Management)

**Purpose:** Manage queue display monitors

**Laravel Migration:**
```php
Schema::create('queue_displays', function (Blueprint $table) {
    $table->id();
    
    // Display Information
    $table->string('name'); // "OB Monitor 1"
    $table->foreignId('consultation_type_id')->constrained()->onDelete('cascade');
    $table->string('location')->nullable(); // "Waiting Area A"
    
    // Display Settings
    $table->json('display_settings')->nullable(); // Custom settings (font size, color, etc.)
    
    // Status
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_heartbeat')->nullable(); // Monitor online status
    
    // Access
    $table->string('access_token', 64)->unique(); // Secure token for display access
    
    $table->timestamps();
});
```

**Model:**
```php
class QueueDisplay extends Model
{
    protected $casts = [
        'display_settings' => 'array',
        'last_heartbeat' => 'datetime',
    ];
    
    public function consultationType() {
        return $this->belongsTo(ConsultationType::class);
    }
    
    // Generate access token
    protected static function boot() {
        parent::boot();
        
        static::creating(function ($display) {
            $display->access_token = bin2hex(random_bytes(32));
        });
    }
    
    // Check if display is online
    public function isOnline() {
        if (!$this->last_heartbeat) return false;
        
        return $this->last_heartbeat->gt(now()->subMinutes(5));
    }
    
    // Update heartbeat
    public function heartbeat() {
        $this->last_heartbeat = now();
        $this->save();
    }
    
    // Get current queue for this display
    public function getCurrentQueue() {
        return Queue::where('consultation_type_id', $this->consultation_type_id)
            ->where('queue_date', today())
            ->where('status', 'serving')
            ->first();
    }
    
    // Get upcoming queues
    public function getUpcomingQueues($limit = 5) {
        return Queue::where('consultation_type_id', $this->consultation_type_id)
            ->where('queue_date', today())
            ->where('status', 'waiting')
            ->orderBy('queue_number')
            ->limit($limit)
            ->get();
    }
}
```

**Display URL Example:**
```
https://hospital.com/display?token=abc123xyz789...

// Or named routes
https://hospital.com/display/ob?token=abc123
https://hospital.com/display/pedia?token=def456
```

**Display Settings JSON Example:**
```json
{
    "font_size": "large",
    "theme": "dark",
    "show_estimated_time": true,
    "show_patient_count": true,
    "auto_scroll": false,
    "sound_enabled": true,
    "volume": 80
}
```

---

## 16. notifications (Laravel Notifications)

**Purpose:** Push notifications via Reverb

**Laravel Migration:**
```php
Schema::create('notifications', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('type');
    $table->morphs('notifiable'); // Works with User model
    $table->text('data'); // JSON notification data
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
    
    $table->index(['notifiable_type', 'notifiable_id']);
});
```

**Notification Types:**
- `AppointmentApproved` - Appointment approved with queue number
- `AppointmentDeclined` - Appointment declined with reason
- `AppointmentReminder` - 1 day / 1 hour before
- `QueueNearby` - Your turn is coming (2-3 away)
- `QueueCalled` - Your turn now!
- `AppointmentCancelled` - Appointment cancelled

**Example Notification:**
```php
namespace App\Notifications;

class AppointmentApproved extends Notification implements ShouldBroadcast
{
    public function __construct(public Appointment $appointment) {}
    
    public function via($notifiable) {
        return ['database', 'broadcast'];
    }
    
    public function toArray($notifiable) {
        return [
            'appointment_id' => $this->appointment->id,
            'queue_number' => $this->appointment->queue->formatted_number,
            'appointment_date' => $this->appointment->appointment_date,
            'estimated_time' => $this->appointment->queue->estimated_time,
            'message' => "Your appointment has been approved! Queue: {$this->appointment->queue->formatted_number}",
        ];
    }
    
    public function toBroadcast($notifiable) {
        return new BroadcastMessage([
            'appointment_id' => $this->appointment->id,
            'queue_number' => $this->appointment->queue->formatted_number,
        ]);
    }
}
```

**Usage:**
```php
// Send notification
$user->notify(new AppointmentApproved($appointment));

// Broadcast to mobile app
broadcast(new AppointmentApprovedEvent($user, $appointment));
```

---

## Spatie Permission Tables

**Auto-created by package migration:**
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

**Creates:**
- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

**Role Seeder:**
```php
class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $patient = Role::create(['name' => 'patient', 'guard_name' => 'web']);
        $nurse = Role::create(['name' => 'nurse', 'guard_name' => 'web']);
        $doctor = Role::create(['name' => 'doctor', 'guard_name' => 'web']);
        $cashier = Role::create(['name' => 'cashier', 'guard_name' => 'web']);
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        
        // Create permissions
        $permissions = [
            // Appointments
            'view-appointments',
            'create-appointments',
            'approve-appointments',
            'decline-appointments',
            'cancel-appointments',
            
            // Queue
            'manage-queue',
            'call-queue',
            'skip-queue',
            
            // Medical
            'input-vital-signs',
            'view-medical-records',
            'add-diagnosis',
            'add-prescription',
            
            // Billing
            'view-billing',
            'process-billing',
            'apply-discount',
            
            // Admission
            'admit-patient',
            'discharge-patient',
            
            // Reports
            'view-reports',
            
            // System
            'manage-users',
            'manage-system-settings',
            'manage-displays',
        ];
        
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }
        
        // Assign permissions to roles
        $nurse->givePermissionTo([
            'view-appointments',
            'approve-appointments',
            'decline-appointments',
            'manage-queue',
            'call-queue',
            'skip-queue',
            'input-vital-signs',
            'view-medical-records',
        ]);
        
        $doctor->givePermissionTo([
            'view-appointments',
            'view-medical-records',
            'add-diagnosis',
            'add-prescription',
            'apply-discount',
            'admit-patient',
            'discharge-patient',
        ]);
        
        $cashier->givePermissionTo([
            'view-billing',
            'process-billing',
        ]);
        
        $admin->givePermissionTo(Permission::all());
    }
}
```

---

## Database Relationships Diagram

```
USERS (Patients + Staff)
  ├─→ has many MEDICAL_RECORDS
  ├─→ has many APPOINTMENTS
  ├─→ has many QUEUES
  ├─→ has many BILLING_TRANSACTIONS
  ├─→ has many ADMISSIONS
  ├─→ has many DOCTOR_SCHEDULES (if doctor)
  ├─→ belongs to many CONSULTATION_TYPES (if doctor)
  └─→ belongs to many ROLES (Spatie)

CONSULTATION_TYPES
  ├─→ has many APPOINTMENTS
  ├─→ has many QUEUES
  ├─→ has many MEDICAL_RECORDS
  ├─→ has many DOCTOR_SCHEDULES
  ├─→ has many QUEUE_DISPLAYS
  └─→ belongs to many USERS (doctors)

APPOINTMENTS
  ├─→ belongs to USER (patient)
  ├─→ belongs to CONSULTATION_TYPE
  ├─→ belongs to USER (doctor)
  ├─→ has one QUEUE
  └─→ has one MEDICAL_RECORD

QUEUES
  ├─→ belongs to APPOINTMENT (nullable - walk-in)
  ├─→ belongs to USER (patient)
  ├─→ belongs to CONSULTATION_TYPE
  ├─→ belongs to USER (doctor)
  └─→ has one MEDICAL_RECORD

MEDICAL_RECORDS
  ├─→ belongs to USER (patient)
  ├─→ belongs to CONSULTATION_TYPE
  ├─→ belongs to APPOINTMENT (nullable)
  ├─→ belongs to QUEUE (nullable)
  ├─→ has many PRESCRIPTIONS
  └─→ has one BILLING_TRANSACTION

BILLING_TRANSACTIONS
  ├─→ belongs to USER (patient)
  ├─→ belongs to MEDICAL_RECORD
  └─→ has many BILLING_ITEMS

BILLING_ITEMS
  ├─→ belongs to BILLING_TRANSACTION
  ├─→ belongs to SERVICE (nullable)
  └─→ belongs to HOSPITAL_DRUG (nullable)

ADMISSIONS
  ├─→ belongs to USER (patient)
  ├─→ belongs to MEDICAL_RECORD
  └─→ belongs to USER (admitted_by doctor)

QUEUE_DISPLAYS
  └─→ belongs to CONSULTATION_TYPE
```

---

## Complete Data Flow

**ONLINE PATIENT JOURNEY:**
```
1. User registers → users table (role: patient)
2. User books appointment → appointments (status: pending)
3. Nurse reviews → appointments (status: approved)
4. Queue auto-generated → queues (with estimated time)
5. Notification sent → notifications
6. Patient arrives → appointments (status: checked_in)
7. Nurse calls queue → queues (status: called) + Reverb broadcast
8. Nurse interviews → chief_complaints_updated
9. Nurse inputs vitals → medical_records created
10. Forward to doctor → queues (status: serving)
11. Doctor examines → medical_records (diagnosis, prescription_notes)
12. Doctor prescribes → prescriptions created
13. Doctor decides:
    ├─→ OUTPATIENT → Forward to billing
    │   └─→ billing_transactions + billing_items
    │   └─→ Cashier processes → billing_transactions (status: paid)
    │   └─→ Patient discharged → queues (status: completed)
    │
    └─→ ADMISSION → Forward to admission
        └─→ admissions created
        └─→ Billing handled by external system
```

**WALK-IN PATIENT JOURNEY:**
```
1. Patient arrives at hospital
2. Nurse creates user account → users (temp password/SMS OTP)
3. Nurse creates appointment → appointments (auto-approved, source: walk-in)
4. Queue auto-generated → queues
5. Same flow as online from step 7 onwards
```

---

## Migration Order

**Run in this exact order:**

```bash
1. users
2. consultation_types
3. doctor_consultation_types
4. doctor_schedules
5. appointments
6. queues
7. medical_records
8. prescriptions
9. services
10. hospital_drugs
11. billing_transactions
12. billing_items
13. admissions
14. system_settings
15. queue_displays
16. notifications
17. Spatie migrations (auto-run)
```

---

## Data Retention Policy

```php
// Standard for Medical Systems

NEVER DELETE (Keep Forever):
✅ users (soft delete only)
✅ medical_records (legal requirement)
✅ prescriptions (medical history)
✅ billing_transactions (audit/tax - 7+ years)
✅ admissions (medical history)

ARCHIVE AFTER 90 DAYS:
✅ appointments (status: completed)
✅ queues (status: completed)

AUTO-DELETE AFTER 30 DAYS:
✅ appointments (status: cancelled, no_show, declined)
✅ notifications (read)

DAILY CLEANUP:
✅ queues (reset daily - archive old ones)
✅ system logs (keep 30 days)
```

**Implementation:**
```php
// Scheduled Job
class ArchiveOldRecords extends Command
{
    public function handle()
    {
        // Archive old completed appointments
        Appointment::where('status', 'completed')
            ->where('updated_at', '<', now()->subDays(90))
            ->update(['archived' => true]);
        
        // Delete old cancelled appointments
        Appointment::whereIn('status', ['cancelled', 'no_show', 'declined'])
            ->where('updated_at', '<', now()->subDays(30))
            ->delete();
        
        // Delete read notifications older than 30 days
        DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays(30))
            ->delete();
    }
}
```

---

## Security Considerations

### Authentication
- Patients: Email/Phone + Password (Laravel Sanctum for API)
- Staff: Email + Password (web portal)
- 2FA optional for admin accounts

### Authorization
- Spatie Permission for role-based access
- Row-level security via policies
- Doctors can only see their assigned patients
- Patients can only see their own records

### Data Protection
- Password: Bcrypt/Argon2
- Sensitive fields: Consider Laravel's encrypted casting
- API tokens: Sanctum token management
- HTTPS required for production

### Audit Trail
- Activity logs for sensitive operations
- Track who accessed medical records
- Track billing changes
- Track discount approvals

### HIPAA/Data Privacy Compliance
- Never hard-delete medical records
- Encryption at rest (database level)
- Encryption in transit (HTTPS)
- Regular backups with encryption
- Access logging
- Data retention policies

---

## Performance Optimization

### Indexes
```php
// Already included in migrations, but summary:

users:
- phone, email (unique)
- is_active

appointments:
- user_id, doctor_id
- appointment_date, status
- consultation_type_id

queues:
- queue_date, status
- consultation_type_id
- user_id, doctor_id
- unique(queue_number, queue_date, consultation_type_id)

medical_records:
- user_id, visit_date
- consultation_type_id
- doctor_id

billing_transactions:
- user_id
- transaction_number (unique)
- transaction_date
- payment_status
```

### Caching Strategy
```php
// Cache frequently accessed data
Cache::remember('consultation-types', 3600, function() {
    return ConsultationType::where('is_active', true)->get();
});

Cache::remember('system-settings', 3600, function() {
    return SystemSetting::all()->pluck('setting_value', 'setting_key');
});

// Invalidate when queue changes
event(new QueueUpdated($queue)); // Clear queue cache
```

### Database Load
- Expected daily load: 100-200 patients
- Expected concurrent users: 20-50
- Expected queue operations: Real-time (Reverb handles)
- Expected billing operations: 100-200 transactions/day

### Recommendations
- Use Redis for Reverb and session storage
- Use database read replicas for reports
- Archive old data regularly
- Monitor slow queries

---

## Summary of Key Decisions

### ✅ Confirmed Design Choices:

1. **Single users table** - Everyone (patients + staff)
2. **Flexible consultation types** - Can add new types easily
3. **Queue format** - O-1, P-1, G-1 (short prefix)
4. **Queue timing** - Auto-estimated with real-time updates
5. **Medical records** - All-in-one table (vitals + diagnosis)
6. **Chief complaints** - Two fields (initial + updated)
7. **Prescriptions** - Separate table (multiple per visit)
8. **Walk-in flow** - Always creates user account
9. **Billing** - Itemized with discount support
10. **Admissions** - Track only (external billing)
11. **Services** - Real hospital pricing
12. **Queue displays** - Managed via database
13. **System settings** - Configurable via database
14. **Notifications** - Laravel + Reverb
15. **Permissions** - Spatie package

---

## Next Steps

After DATABASE.md approval:

1. ✅ **ROLES.md** - Detailed Spatie permission matrix
2. ✅ **API.md** - All Sanctum endpoints for Flutter
3. ✅ **EVENTS.md** - Reverb broadcasting events
4. ✅ **USER-FLOWS.md** - Complete step-by-step workflows
5. ✅ **UI-WIREFRAMES.md** - Screen layouts

---

*Document Version: FINAL 1.0*  
*Last Updated: January 18, 2026*  
*Status: Ready for Development*
