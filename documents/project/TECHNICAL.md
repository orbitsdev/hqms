# Technical Documentation

## Hospital Queue Management System (HQMS)

**Project Name:** CareTime
**Version:** 1.0.0
**Type:** Full-Stack Web Application
**Client:** Guardiano Maternity and Children Clinic and Hospital

---

## 1. Project Overview

### 1.1 Description

HQMS is a comprehensive hospital queue management system designed for a maternity and children's clinic. The system streamlines patient flow from appointment booking through consultation to billing, with real-time queue displays and multi-role support.

### 1.2 Key Features

- **Online Appointment Booking** - Patients can book appointments for themselves or dependents
- **Walk-in Registration** - Nurses can register walk-in patients directly
- **Real-time Queue Management** - Live queue status with WebSocket updates
- **Patient Triage** - Nurses record vital signs and forward patients to doctors
- **Medical Examination** - Doctors conduct examinations, add diagnoses, prescriptions
- **Billing System** - Cashiers process payments with discounts (Senior/PWD/Employee)
- **Queue Display** - Public TV monitors showing current queue status
- **Role-based Access Control** - 5 distinct user roles with specific permissions

### 1.3 User Roles

| Role | Description |
|------|-------------|
| **Patient** | Book appointments, view queue status, access medical records |
| **Nurse** | Manage appointments, triage patients, record vitals, manage queue |
| **Doctor** | Examine patients, diagnose, prescribe, manage admissions |
| **Cashier** | Process billing, apply discounts, generate receipts |
| **Admin** | Manage users, services, drugs inventory, system settings |

---

## 2. Technology Stack

### 2.1 Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | 8.2+ | Server-side language |
| Laravel | 12.x | PHP Framework |
| Livewire | 4.0 | Full-stack reactive components |
| Laravel Fortify | 1.x | Authentication (login, register, 2FA) |
| Laravel Sanctum | 4.x | API authentication (for mobile app) |
| Laravel Reverb | 1.x | WebSocket server for real-time updates |
| Spatie Permission | 6.x | Role-based access control |
| Spatie Laravel PDF | 1.x | PDF generation (medical records) |

### 2.2 Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| Flux UI | 2.9 | Livewire component library |
| Tailwind CSS | 4.0 | Utility-first CSS framework |
| Alpine.js | (via Livewire) | JavaScript interactivity |
| Laravel Echo | 2.x | WebSocket client |
| Vite | 7.x | Frontend build tool |

### 2.3 Database

| Environment | Database |
|-------------|----------|
| Development | SQLite |
| Production | MySQL 8.0 |

### 2.4 Additional Services

| Service | Purpose |
|---------|---------|
| Redis | Cache and session storage (production) |
| Supervisor | Queue worker and Reverb process manager |
| Chromium | PDF rendering engine |

---

## 3. Folder Structure

```
hqms/
├── app/
│   ├── Actions/                    # Fortify authentication actions
│   │   ├── Fortify/
│   │   │   ├── CreateNewUser.php
│   │   │   ├── PasswordValidationRules.php
│   │   │   ├── ResetUserPassword.php
│   │   │   └── UpdateUserPassword.php
│   │
│   ├── Concerns/                   # Shared validation traits
│   │   └── ProfileValidationRules.php
│   │
│   ├── Events/                     # Broadcast events
│   │   └── QueueUpdated.php        # Real-time queue updates
│   │
│   ├── Http/
│   │   ├── Controllers/            # API controllers
│   │   └── Middleware/
│   │       └── EnsurePersonalInfoComplete.php
│   │
│   ├── Livewire/                   # Livewire components (see Section 5)
│   │   ├── Actions/
│   │   ├── Admin/
│   │   ├── Cashier/
│   │   ├── Display/
│   │   ├── Doctor/
│   │   ├── Nurse/
│   │   ├── Patient/
│   │   └── Settings/
│   │
│   ├── Models/                     # Eloquent models (see Section 4)
│   │
│   ├── Notifications/
│   │   └── GenericNotification.php # Flexible notification system
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── FortifyServiceProvider.php
│   │
│   └── Traits/
│       └── Models/                 # Model relationship traits (see Section 4.2)
│
├── bootstrap/
│   ├── app.php                     # Application bootstrap, middleware
│   └── providers.php               # Service providers
│
├── config/                         # Configuration files
│
├── database/
│   ├── factories/                  # Model factories for testing
│   ├── migrations/                 # Database migrations (27 files)
│   └── seeders/                    # Database seeders
│
├── documents/                      # Project documentation
│   ├── project/                    # Technical documentation
│   └── productionsetup/            # Deployment guides
│
├── public/                         # Public assets
│
├── resources/
│   ├── css/
│   │   └── app.css                 # Tailwind entry point
│   ├── js/
│   │   ├── app.js                  # Main JS entry
│   │   └── echo.js                 # WebSocket configuration
│   └── views/
│       ├── components/             # Blade components
│       ├── layouts/                # Layout templates
│       ├── livewire/               # Livewire component views
│       └── partials/               # Shared partials
│
├── routes/
│   ├── api.php                     # API routes (Sanctum)
│   ├── channels.php                # Broadcast channels
│   ├── console.php                 # Artisan commands
│   ├── settings.php                # Settings routes
│   └── web.php                     # Web routes
│
├── storage/                        # Logs, cache, uploads
│
├── tests/
│   ├── Feature/                    # Feature tests (Pest)
│   └── Unit/                       # Unit tests (Pest)
│
├── .env                            # Environment configuration
├── composer.json                   # PHP dependencies
├── package.json                    # Node dependencies
├── tailwind.config.js              # Tailwind configuration
└── vite.config.js                  # Vite configuration
```

---

## 4. Data Models

### 4.1 Entity Relationship Diagram (Simplified)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              USER MANAGEMENT                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────┐         ┌─────────────────────┐                               │
│  │  User   │────────▶│ PersonalInformation │                               │
│  │         │   1:1   │                     │                               │
│  └────┬────┘         └─────────────────────┘                               │
│       │                                                                     │
│       │ HasRoles (Spatie)                                                  │
│       ▼                                                                     │
│  ┌─────────┐                                                               │
│  │  Role   │  (patient, nurse, doctor, cashier, admin)                     │
│  └─────────┘                                                               │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              APPOINTMENT FLOW                                │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────┐       ┌─────────┐       ┌───────────────┐             │
│  │ ConsultationType│◀──────│  User   │──────▶│  Appointment  │             │
│  │  (Pediatrics,   │  M:N  │(Doctor) │  1:N  │               │             │
│  │   Obstetrics)   │       └─────────┘       └───────┬───────┘             │
│  └────────┬────────┘                                 │                      │
│           │                                          │                      │
│           │ 1:N                                      │ 1:1                  │
│           ▼                                          ▼                      │
│  ┌─────────────────┐                        ┌───────────────┐              │
│  │ DoctorSchedule  │                        │    Queue      │              │
│  │ (availability)  │                        │               │              │
│  └─────────────────┘                        └───────┬───────┘              │
│                                                     │                       │
│                                                     │ 1:1                   │
│                                                     ▼                       │
│                                             ┌───────────────┐              │
│                                             │ MedicalRecord │              │
│                                             │ (self-contained│              │
│                                             │  patient data) │              │
│                                             └───────┬───────┘              │
│                                                     │                       │
│                                     ┌───────────────┼───────────────┐      │
│                                     │               │               │      │
│                                     ▼               ▼               ▼      │
│                              ┌────────────┐ ┌─────────────┐ ┌───────────┐  │
│                              │Prescription│ │  Admission  │ │ Billing   │  │
│                              │            │ │             │ │Transaction│  │
│                              └────────────┘ └─────────────┘ └─────┬─────┘  │
│                                                                   │        │
│                                                                   │ 1:N    │
│                                                                   ▼        │
│                                                            ┌─────────────┐ │
│                                                            │ BillingItem │ │
│                                                            └─────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              REFERENCE DATA                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────┐    ┌──────────────┐    ┌───────────────┐                  │
│  │   Service   │    │ HospitalDrug │    │ SystemSetting │                  │
│  │ (billable)  │    │  (inventory) │    │ (config)      │                  │
│  └─────────────┘    └──────────────┘    └───────────────┘                  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 4.2 Models and Relationships

#### Core Models

| Model | Table | Description | Key Relations |
|-------|-------|-------------|---------------|
| `User` | users | Authentication & account | hasOne PersonalInformation, hasMany Appointments, hasMany MedicalRecords |
| `PersonalInformation` | personal_information | Extended user profile | belongsTo User |
| `Appointment` | appointments | Booking requests | belongsTo User, belongsTo ConsultationType, hasOne Queue |
| `Queue` | queues | Daily queue entries | belongsTo Appointment, hasOne MedicalRecord |
| `MedicalRecord` | medical_records | Visit records (self-contained patient data) | belongsTo Queue, hasMany Prescriptions, hasOne BillingTransaction |
| `Prescription` | prescriptions | Medications prescribed | belongsTo MedicalRecord, belongsTo HospitalDrug |
| `BillingTransaction` | billing_transactions | Payment records | belongsTo MedicalRecord, hasMany BillingItems |
| `BillingItem` | billing_items | Line items in bill | belongsTo BillingTransaction |
| `Admission` | admissions | Hospital admissions | belongsTo MedicalRecord, belongsTo User |

#### Reference Models

| Model | Table | Description |
|-------|-------|-------------|
| `ConsultationType` | consultation_types | Service categories (Pediatrics, Obstetrics, etc.) |
| `DoctorSchedule` | doctor_schedules | Doctor availability by day/time |
| `Service` | services | Billable services with prices |
| `HospitalDrug` | hospital_drugs | Drug inventory with prices |
| `SystemSetting` | system_settings | Application configuration |

### 4.3 Traits Architecture

Each model uses a dedicated trait for relationships and accessors:

```
app/Traits/Models/
├── AdmissionRelations.php
├── AppointmentRelations.php
├── BillingItemRelations.php
├── BillingTransactionRelations.php
├── ConsultationTypeRelations.php
├── DoctorScheduleRelations.php
├── HospitalDrugRelations.php
├── MedicalRecordRelations.php          # Most complex - see below
├── PersonalInformationRelations.php
├── PrescriptionRelations.php
├── QueueDisplayRelations.php
├── QueueRelations.php                   # Includes scopes (today, waiting, serving)
├── ServiceRelations.php
├── SystemSettingMethods.php             # Static get/set methods
└── UserRelations.php                    # User role relations
```

#### Example: MedicalRecordRelations Trait

```php
trait MedicalRecordRelations
{
    // Relationships
    public function user(): BelongsTo;           // Account owner
    public function consultationType(): BelongsTo;
    public function doctor(): BelongsTo;         // Examining doctor
    public function nurse(): BelongsTo;          // Triage nurse
    public function appointment(): BelongsTo;
    public function queue(): BelongsTo;
    public function prescriptions(): HasMany;
    public function billingTransaction(): HasOne;

    // Accessors
    public function getPatientFullNameAttribute(): string;
    public function getPatientFullAddressAttribute(): string;
    public function getPatientAgeAttribute(): ?int;
    public function getEffectiveChiefComplaintsAttribute(): ?string;

    // Scopes
    public function scopeForPatient(Builder $query, ...): Builder;
}
```

#### Example: QueueRelations Trait

```php
trait QueueRelations
{
    // Relationships
    public function appointment(): BelongsTo;
    public function user(): BelongsTo;
    public function consultationType(): BelongsTo;
    public function doctor(): BelongsTo;
    public function servedBy(): BelongsTo;       // Nurse who served
    public function medicalRecord(): HasOne;

    // Accessors
    public function getFormattedNumberAttribute(): string;  // "PED-001"
    public function getWaitTimeAttribute(): ?int;
    public function getServiceTimeAttribute(): ?int;

    // Scopes
    public function scopeToday(Builder $query): Builder;
    public function scopeWaiting(Builder $query): Builder;
    public function scopeServing(Builder $query): Builder;
}
```

### 4.4 Key Design Decisions

#### Self-Contained Medical Records

Patient information is **copied** into each `medical_records` entry rather than referenced:

```php
// medical_records table stores:
- patient_first_name
- patient_last_name
- patient_date_of_birth
- patient_gender
- patient_phone
- patient_address fields
- relationship_to_account  // 'self', 'child', 'spouse', etc.
```

**Rationale:**
1. Allows booking for dependents (parent books for child)
2. Preserves patient info at time of visit (historical accuracy)
3. Each record is self-contained and immutable

---

## 5. Module Structure

### 5.1 Livewire Components by Module

```
app/Livewire/
│
├── Patient/                          # Patient Portal
│   ├── Dashboard.php                 # Overview, queue status, appointments
│   ├── Profile.php                   # Personal information management
│   ├── BookAppointment.php           # Multi-step booking wizard
│   ├── Appointments.php              # Appointment list
│   ├── AppointmentShow.php           # Appointment details
│   ├── ActiveQueue.php               # Real-time queue status
│   ├── MedicalRecords.php            # Visit history list
│   └── MedicalRecordShow.php         # Visit details
│
├── Nurse/                            # Nurse Station
│   ├── Dashboard.php                 # Stats, alerts, doctor availability
│   ├── DoctorSchedules.php           # View doctor schedules
│   ├── Appointments.php              # Manage appointments
│   ├── AppointmentShow.php           # Approve/reject appointments
│   ├── WalkInRegistration.php        # Register walk-in patients
│   ├── TodayQueue.php                # Queue management (1200+ lines)
│   │   └── Features:
│   │       - Call/serve/skip patients
│   │       - Patient interview (5-step form)
│   │       - Vital signs recording
│   │       - Priority management
│   │       - Forward to doctor
│   ├── MedicalRecords.php            # Search all records
│   ├── PatientHistory.php            # Patient lookup
│   └── Admissions.php                # Manage admissions
│
├── Doctor/                           # Doctor Station
│   ├── Dashboard.php                 # Queue stats, waiting patients
│   ├── PatientQueue.php              # Forwarded patients list
│   ├── Examination.php               # Conduct examination (460+ lines)
│   │   └── Features:
│   │       - HPI/Physical exam notes
│   │       - Diagnosis entry
│   │       - Prescription management
│   │       - Hospital drug selection
│   │       - Admission creation
│   │       - PDF export
│   ├── PatientHistory.php            # Lookup patient history
│   ├── MySchedule.php                # View own schedule
│   └── Admissions.php                # Manage admitted patients
│
├── Cashier/                          # Cashier Desk
│   ├── Dashboard.php                 # Billing stats
│   ├── BillingQueue.php              # Patients awaiting payment
│   ├── ProcessBilling.php            # Process payment (470+ lines)
│   │   └── Features:
│   │       - Auto-load consultation fee
│   │       - Add prescriptions/services
│   │       - Apply discounts (Senior/PWD/Employee)
│   │       - Emergency fees
│   │       - Multiple payment methods
│   │       - Receipt generation
│   └── PaymentHistory.php            # Transaction history
│
├── Admin/                            # Admin Console
│   ├── Dashboard.php                 # User statistics
│   ├── UserManagement.php            # CRUD users, assign roles
│   ├── ServiceManagement.php         # Manage billable services
│   └── HospitalDrugManagement.php    # Drug inventory
│
├── Display/                          # Public Displays
│   └── QueueMonitor.php              # TV queue display
│       └── Features:
│           - Called queue numbers
│           - Currently serving
│           - Next in line
│           - Real-time via Echo
│
├── Settings/                         # User Settings
│   ├── Profile.php                   # Account settings
│   ├── Password.php                  # Change password
│   ├── TwoFactor.php                 # 2FA setup
│   └── Appearance.php                # Theme settings
│
└── NotificationDropdown.php          # Global notification bell
```

### 5.2 Route Structure

```php
// routes/web.php

// Patient Portal (/patient/*)
Route::prefix('patient')->middleware(['auth', 'role:patient'])->group(function () {
    Route::get('/', Dashboard::class);
    Route::get('/profile', Profile::class);
    Route::get('/appointments', Appointments::class);
    Route::get('/appointments/book', BookAppointment::class);
    Route::get('/appointments/{appointment}', AppointmentShow::class);
    Route::get('/records', MedicalRecords::class);
    Route::get('/records/{medicalRecord}', MedicalRecordShow::class);
    Route::get('/queue', ActiveQueue::class);
});

// Nurse Station (/nurse/*)
Route::prefix('nurse')->middleware(['auth', 'role:nurse'])->group(function () {
    Route::get('/', Dashboard::class);
    Route::get('/appointments', Appointments::class);
    Route::get('/appointments/{appointment}', AppointmentShow::class);
    Route::get('/walk-in', WalkInRegistration::class);
    Route::get('/queue', TodayQueue::class);
    Route::get('/doctor-schedules', DoctorSchedules::class);
    Route::get('/medical-records', MedicalRecords::class);
    Route::get('/patient-history', PatientHistory::class);
    Route::get('/admissions', Admissions::class);
});

// Doctor Station (/doctor/*)
Route::prefix('doctor')->middleware(['auth', 'role:doctor'])->group(function () {
    Route::get('/', Dashboard::class);
    Route::get('/queue', PatientQueue::class);
    Route::get('/examine/{queue}', Examination::class);
    Route::get('/patient-history', PatientHistory::class);
    Route::get('/schedule', MySchedule::class);
    Route::get('/admissions', Admissions::class);
});

// Cashier Desk (/cashier/*)
Route::prefix('cashier')->middleware(['auth', 'role:cashier'])->group(function () {
    Route::get('/', Dashboard::class);
    Route::get('/queue', BillingQueue::class);
    Route::get('/process/{medicalRecord}', ProcessBilling::class);
    Route::get('/history', PaymentHistory::class);
});

// Admin Console (/admin/*)
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', Dashboard::class);
    Route::get('/users', UserManagement::class);
    Route::get('/services', ServiceManagement::class);
    Route::get('/drugs', HospitalDrugManagement::class);
});

// Queue Display (public)
Route::get('/display/queue/{type?}', QueueMonitor::class);
```

---

## 6. Patient Flow

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                            PATIENT JOURNEY                                    │
└──────────────────────────────────────────────────────────────────────────────┘

    ┌─────────────┐
    │   Patient   │
    └──────┬──────┘
           │
           ▼
    ┌─────────────────┐         ┌─────────────────┐
    │ Book Appointment│         │   Walk-in       │
    │   (Online)      │         │ (Nurse registers)│
    └────────┬────────┘         └────────┬────────┘
             │                           │
             │    Appointment Created    │
             └───────────┬───────────────┘
                         │
                         ▼
              ┌─────────────────────┐
              │  Nurse: Approve &   │
              │  Check-in Patient   │
              │  → Queue Created    │
              └──────────┬──────────┘
                         │
                         ▼
              ┌─────────────────────┐
              │  Nurse: Call Patient│
              │  → Status: called   │
              └──────────┬──────────┘
                         │
                         ▼
              ┌─────────────────────┐
              │  Nurse: Interview   │
              │  - Record vitals    │
              │  - Update complaints│
              │  → Status: serving  │
              └──────────┬──────────┘
                         │
                         ▼
              ┌─────────────────────┐
              │  Nurse: Forward to  │
              │  Doctor             │
              │  → Status: waiting  │
              │    (for doctor)     │
              └──────────┬──────────┘
                         │
                         ▼
              ┌─────────────────────┐
              │  Doctor: Examine    │
              │  - HPI/PE           │
              │  - Diagnosis        │
              │  - Prescriptions    │
              │  → Status: completed│
              │  → Record: for_billing│
              └──────────┬──────────┘
                         │
                         ▼
              ┌─────────────────────┐
              │  Cashier: Process   │
              │  - Add items        │
              │  - Apply discounts  │
              │  - Receive payment  │
              │  → Record: completed│
              └──────────┬──────────┘
                         │
                         ▼
              ┌─────────────────────┐
              │  Patient: Done      │
              │  - View record      │
              │  - Download PDF     │
              └─────────────────────┘
```

---

## 7. Queue Status Flow

```
Queue Status Transitions:

    ┌──────────┐
    │ waiting  │  Initial state after check-in
    └────┬─────┘
         │
         │ Nurse calls patient
         ▼
    ┌──────────┐
    │  called  │  Patient's number displayed on screen
    └────┬─────┘
         │
         │ Patient arrives, nurse starts interview
         ▼
    ┌──────────┐
    │ serving  │  Vitals being recorded
    └────┬─────┘
         │
         ├──────────────────────────────┐
         │                              │
         │ Forwarded to doctor          │ Patient didn't show
         ▼                              ▼
    ┌──────────┐                  ┌──────────┐
    │ waiting  │                  │ skipped  │
    │(for doc) │                  └──────────┘
    └────┬─────┘
         │
         │ Doctor completes exam
         ▼
    ┌──────────┐
    │completed │
    └──────────┘

Medical Record Status:

    draft → for_triage → examining → for_billing → completed
```

---

## 8. Real-time Features

### 8.1 WebSocket Configuration

```php
// app/Events/QueueUpdated.php
class QueueUpdated implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [
            new Channel('queue.display.' . $this->consultationTypeId),
            new Channel('queue.display.all'),
        ];
    }
}
```

### 8.2 Components Listening to Events

| Component | Channel | Purpose |
|-----------|---------|---------|
| `QueueMonitor` | queue.display.{type} | TV display updates |
| `Patient\ActiveQueue` | queue.patient.{userId} | Patient's queue status |
| `Nurse\TodayQueue` | queue.nurse | Queue changes |
| `Nurse\Dashboard` | queue.nurse | Stats updates |

---

## 9. Commands Reference

### Development

```bash
# Start development server (Laravel + Queue + Reverb + Vite)
composer dev

# Code formatting
composer lint

# Run tests
composer test
```

### Production

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache

# Queue management
php artisan queue:work
php artisan queue:restart

# Reverb WebSocket server
php artisan reverb:start
```

---

## 10. Environment Variables

Key environment variables for configuration:

```env
# Application
APP_ENV=local|production
APP_DEBUG=true|false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=sqlite|mysql
DB_DATABASE=hqms

# Queue & Broadcasting
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=reverb
REVERB_APP_KEY=your-key
REVERB_APP_SECRET=your-secret

# Session
SESSION_DRIVER=database|redis
SESSION_SECURE_COOKIE=false|true
```

See `documents/productionsetup/05-ENV-PRODUCTION.md` for full production configuration.

---

## 11. Testing

### Test Structure

```
tests/
├── Feature/
│   ├── Auth/                    # Authentication tests
│   ├── Patient/                 # Patient module tests
│   ├── Nurse/                   # Nurse module tests
│   └── ...
└── Unit/
    └── Models/                  # Model unit tests
```

### Running Tests

```bash
# All tests
php artisan test

# Specific file
php artisan test tests/Feature/Auth/LoginTest.php

# With filter
php artisan test --filter="can book appointment"
```

---

## 12. Additional Resources

- [Production Setup Guide](../productionsetup/README.md)
- [Database Schema](./DATABASE.md)
- [API Documentation](./API.md) (for mobile app)
