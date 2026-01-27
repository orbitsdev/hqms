# Nurse Module Documentation

> Hospital Queue Management System (HQMS) - Nurse Station Features

## Overview

The Nurse Module provides comprehensive patient management capabilities for nursing staff at Guardiano Maternity and Children Clinic and Hospital. It handles the complete patient flow from appointment approval through vital signs recording and handoff to doctors.

---

## Routes

| Route | Component | Description |
|-------|-----------|-------------|
| `/nurse/dashboard` | `Dashboard` | Dashboard with stats and quick actions |
| `/nurse/appointments` | `Appointments` | List all appointments with filters |
| `/nurse/queue` | `TodayQueue` | Today's queue management |
| `/nurse/walk-in` | `WalkInRegistration` | Register walk-in patients |
| `/nurse/medical-records` | `MedicalRecords` | View/edit medical records |
| `/nurse/doctor-schedules` | `DoctorSchedules` | Manage doctor availability |

---

## Components

### 1. Dashboard

**File:** `app/Livewire/Nurse/Dashboard.php`
**View:** `resources/views/livewire/nurse/dashboard.blade.php`

#### Statistics Display (6 Key Metrics):
- Pending Appointments
- Today's Appointments
- Check-in Waiting
- In Queue (Waiting)
- Currently Serving
- Completed Today

#### Panels:
- **Currently Serving Panel**: Shows all patients actively being served with queue numbers, names, and consultation types
- **Up Next Panel**: Displays next 5 waiting/called patients prioritized by Emergency > Urgent > Normal

#### Quick Actions:
- Walk-in Registration
- View Queue
- View All Appointments
- View Pending Appointments

---

### 2. Today's Queue Management

**File:** `app/Livewire/Nurse/TodayQueue.php` (~930 lines)
**View:** `resources/views/livewire/nurse/today-queue.blade.php`

#### Queue Display Features:
- Visual queue cards with color-coded status
- Priority badges (Emergency, Urgent)
- Vital signs indicator on serving patients
- Filter by consultation type
- Filter by status (waiting, called, serving, completed, skipped)
- Search by patient name or queue number
- Real-time status counters

#### Queue Actions:

| Current Status | Available Actions |
|----------------|-------------------|
| Waiting | Call, Start Serving, Skip |
| Called | Start Serving, Skip |
| Serving | Patient Interview, Forward to Doctor, Stop Serving |
| Skipped | Requeue |
| Completed | View only |

#### Patient Interview Modal (5-Step Wizard):

**Step 1: Patient Information**
- First/Middle/Last Name (required)
- Date of Birth
- Gender
- Contact Number

**Step 2: Address**
- Province
- Municipality/City
- Barangay
- Street Address
- Zip Code

**Step 3: Companion & Emergency Contact**
- Companion Name/Contact/Relationship
- Emergency Contact Name/Number/Relationship

**Step 4: Medical Background**
- Blood Type
- Allergies
- Chronic Conditions
- Current Medications
- Past Medical History
- Family Medical History

**Step 5: Vital Signs**
- Temperature (°C)
- Blood Pressure (systolic/diastolic)
- Cardiac Rate
- Respiratory Rate
- Weight (kg)
- Height (cm)
- Head Circumference (Pediatrics only)
- Chest Circumference (Pediatrics only)
- Fetal Heart Tone (OB only)
- Fundal Height (OB only)
- Last Menstrual Period (OB only)
- Updated Chief Complaints

#### Forward to Doctor:
- Requires vital signs to be recorded
- Updates queue status to completed
- Notifies assigned doctors
- Patient ready for consultation

---

### 3. Appointments Management

**File:** `app/Livewire/Nurse/Appointments.php` (~410 lines)
**View:** `resources/views/livewire/nurse/appointments.blade.php`

#### Filtering & Search:
- Search by patient name, email, phone, or chief complaints
- Filter by status (pending, approved, cancelled, today)
- Filter by consultation type
- Filter by date
- Filter by source (online, walk-in)
- Sortable columns (appointment date, creation date)

#### Actions:
- **View Details**: Modal showing complete appointment information
- **Approve**:
  - Generates queue number automatically
  - Creates queue entry
  - Sends in-app notification to patient
  - Optional notes field
- **Cancel**:
  - Requires cancellation reason (min 10 characters)
  - Notifies patient
  - Updates related queue if exists

#### Status Counts Display:
- All appointments
- Pending
- Approved
- Today's appointments
- Cancelled

---

### 4. Walk-in Registration

**File:** `app/Livewire/Nurse/WalkInRegistration.php` (~272 lines)
**View:** `resources/views/livewire/nurse/walk-in-registration.blade.php`

#### 4-Step Registration Wizard:

**Step 1: Consultation Type Selection**
- List of active consultation types
- Visual cards with descriptions

**Step 2: Patient Information**
- First/Middle/Last Name (required)
- Date of Birth
- Gender
- Contact Number
- Address (Province, Municipality, Barangay, Street)

**Step 3: Chief Complaints**
- Text area for symptoms/reason for visit

**Step 4: Account Creation (Optional)**
- Create patient account checkbox
- Email input
- Auto-generate password option
- Manual password entry
- Shows generated credentials after creation

#### Output:
- Creates appointment with "pending" status and "walk-in" source
- Optionally creates user account with patient role
- Notifies other nurses of new walk-in

---

### 5. Medical Records

**File:** `app/Livewire/Nurse/MedicalRecords.php` (~776 lines)
**View:** `resources/views/livewire/nurse/medical-records.blade.php`

#### Search & Filters:
- Search by patient name or record number
- Filter by consultation type
- Filter by doctor
- Filter by status (In Progress, For Billing, For Admission, Completed)
- Filter by visit type (New, Old, Revisit)
- Date range filter (default: last 30 days)

#### View Modal (Tabbed Interface):
- Patient information
- Address details
- Companion/Emergency contacts
- Medical background
- Visit details
- Vital signs
- Doctor's notes (if available)

#### Edit Modal (6-Step Wizard):
1. Patient Information
2. Address
3. Companion & Emergency Contact
4. Medical Background
5. Visit Details
6. Vital Signs

#### PDF Export:
- Generate downloadable medical record PDF
- Includes all patient and visit information
- Uses Spatie Laravel PDF with Browsershot
- Auto-deletes temp file after download

#### Statistics:
- Today's records
- This month's records
- In progress count
- For billing count

---

### 6. Doctor Schedules

**File:** `app/Livewire/Nurse/DoctorSchedules.php` (~871 lines)
**View:** `resources/views/livewire/nurse/doctor-schedules.blade.php`

#### Overview Tab:
- Weekly calendar view
- Shows doctor availability by day
- Color-coded by consultation type
- Displays time slots

#### Weekly Schedules Tab:
- Manage recurring schedules
- Set availability by day of week
- Configure time slots (start/end)
- Assign consultation types
- Copy schedules between doctors

#### Exceptions Tab:
- Create schedule exceptions
- Exception types:
  - Annual Leave
  - Sick Leave
  - Holiday
  - Training
  - Emergency Leave
  - Half-day (AM/PM)
  - Extra Clinic
- Date range support (up to 60 days)
- Notes field

#### Statistics:
- Doctors with schedules
- Doctors without schedules
- Upcoming leaves
- Total weekly schedules

---

## Workflows

### Online Booking Flow
```
Patient books online
    ↓
Appointment created (status: pending, source: online)
    ↓
Nurse reviews in Appointments list
    ↓
Nurse approves → Queue created (status: waiting)
    ↓
Patient arrives → Nurse checks in
    ↓
Nurse calls/serves patient → Medical record created
    ↓
Nurse conducts patient interview & records vital signs
    ↓
Nurse forwards to doctor → Doctors notified
```

### Walk-in Flow
```
Patient arrives at clinic
    ↓
Nurse registers via Walk-in form
    ↓
Appointment created (status: pending, source: walk-in)
    ↓
Nurse approves → Queue created (status: waiting)
    ↓
Nurse serves patient → Medical record created
    ↓
Nurse conducts patient interview & records vital signs
    ↓
Nurse forwards to doctor → Doctors notified
```

### Queue Status Progression
```
waiting → called → serving → completed
                ↓
              skipped → (requeue) → waiting
```

### Medical Record Status
```
in_progress → for_billing → completed
           → for_admission → completed
```

---

## Queue Number Generation

Queue numbers are generated per consultation type per day:
- Format: `{short_name}-{number}` (e.g., O-1, O-2, P-1, G-1)
- Each consultation type has its own sequence
- Resets daily

Example for a day:
```
Obstetrics: O-1, O-2, O-3
Pediatrics: P-1, P-2
General: G-1, G-2, G-3, G-4
```

---

## Notifications

| Event | Recipients | Notification Type |
|-------|------------|-------------------|
| Appointment approved | Patient | In-app + Queue number |
| Appointment cancelled | Patient | In-app + Reason |
| Patient called | Patient | In-app |
| Patient ready for doctor | Doctors | In-app |
| Walk-in registered | Other nurses | In-app |

### SMS Notifications (Prepared but Disabled)
SMS functionality is implemented but commented out for later activation:
- Appointment approval SMS
- Appointment cancellation SMS
- Queue status updates

---

## Database Tables Used

| Table | Purpose |
|-------|---------|
| `appointments` | Patient appointment requests |
| `queues` | Queue entries for today |
| `medical_records` | Patient visit records |
| `consultation_types` | Types of consultations (OB, Pedia, etc.) |
| `users` | Nurses, doctors, patients |
| `doctor_schedules` | Weekly recurring schedules |
| `doctor_schedule_exceptions` | Leave, holidays, extra days |
| `notifications` | In-app notifications |

---

## Dependencies

### PHP Packages
- `spatie/laravel-pdf` - PDF generation
- `spatie/browsershot` - HTML to PDF conversion
- `masmerise/livewire-toaster` - Toast notifications

### NPM Packages
- `puppeteer` - Required for PDF generation (Chromium headless browser)

### Installation
```bash
# Install puppeteer for PDF generation
npm install puppeteer
```

---

## File Structure

```
app/
├── Livewire/
│   └── Nurse/
│       ├── Dashboard.php
│       ├── TodayQueue.php
│       ├── Appointments.php
│       ├── MedicalRecords.php
│       ├── WalkInRegistration.php
│       └── DoctorSchedules.php
├── Models/
│   ├── Queue.php
│   ├── Appointment.php
│   ├── MedicalRecord.php
│   ├── ConsultationType.php
│   ├── DoctorSchedule.php
│   └── DoctorScheduleException.php
├── Jobs/
│   └── SendSmsJob.php (prepared, not active)
└── Http/Controllers/Api/
    └── SmsController.php (prepared, not active)

resources/views/
├── livewire/
│   └── nurse/
│       ├── dashboard.blade.php
│       ├── today-queue.blade.php
│       ├── appointments.blade.php
│       ├── medical-records.blade.php
│       ├── walk-in-registration.blade.php
│       └── doctor-schedules.blade.php
└── pdf/
    └── medical-record.blade.php
```

---

## Testing

Test files located in `tests/Feature/Nurse/`:
- `TodayQueueTest.php` - Queue management tests

Run nurse tests:
```bash
php artisan test tests/Feature/Nurse/
```

---

## Recent Fixes & Changes

### 2026-01-27

1. **Patient Interview Modal Fix**
   - Issue: Modal showed blank when queue was filtered by different status
   - Fix: Added `getInterviewQueueProperty()` computed property to fetch queue directly from database
   - Files: `TodayQueue.php`, `today-queue.blade.php`

2. **Save Interview Error Handling**
   - Added try-catch block to catch and display database errors
   - Convert empty strings to null for nullable fields
   - Show toast error message if save fails

3. **SMS Functionality Disabled**
   - Commented out `SendSmsJob` dispatches in `Appointments.php`
   - Commented out SMS dispatch in `SmsController.php`
   - Ready to uncomment when SMS provider is configured

4. **PDF Download Fix**
   - Issue: Livewire components can't return download responses directly
   - Fix: Save PDF to temp file, return download response, auto-delete after send
   - File: `MedicalRecords.php`

5. **Notification Location**
   - Moved notification bell from sidebar to upper right corner (desktop)
   - File: `layouts/app/sidebar.blade.php`

---

## Future Improvements

### Planned
- [ ] SMS notifications for queue updates
- [ ] Real-time updates with Laravel Reverb
- [ ] Dashboard auto-refresh polling
- [ ] "Patients Due Soon" section
- [ ] Average wait time display

### Nice to Have
- [ ] Analytics charts (patient volume trends)
- [ ] Nurse workload metrics
- [ ] Vital signs trends visualization
- [ ] Appointment no-show tracking
- [ ] Peak hour analysis
- [ ] Bulk queue operations

---

## Design Notes

- Minimal color scheme (black, white, gray/zinc)
- Status colors: Blue (waiting), Purple (called), Green (serving), Gray (completed/skipped)
- Priority colors: Red (emergency), Amber (urgent)
- Dark mode support throughout
- Responsive design for mobile and desktop
- Flux UI components for consistent styling

---

*Last Updated: January 27, 2026*
