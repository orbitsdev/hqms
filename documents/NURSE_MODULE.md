# Nurse Module Documentation

## Overview

The nurse module handles patient appointments, walk-in registration, queue management, vital signs recording, and forwarding patients to doctors.

## Routes

| Route | Component | Description |
|-------|-----------|-------------|
| `/nurse` | `NurseDashboard` | Dashboard with stats and quick actions |
| `/nurse/appointments` | `NurseAppointments` | List all appointments with filters |
| `/nurse/appointments/{id}` | `NurseAppointmentShow` | View and manage single appointment |
| `/nurse/queue` | `NurseTodayQueue` | Today's queue management |
| `/nurse/walk-in` | `NurseWalkInRegistration` | Register walk-in patients |

## Components

### 1. Dashboard (`app/Livewire/Nurse/Dashboard.php`)

**Stats displayed:**
- Pending appointments
- Today's appointments
- Waiting for check-in
- In queue (waiting)
- Currently serving
- Completed today

**Features:**
- Currently serving display
- Up next queue preview
- Quick action buttons

### 2. Appointments (`app/Livewire/Nurse/Appointments.php`)

**Filters:**
- Status tabs: Pending, Approved, Today, Cancelled, All
- Consultation type dropdown
- Source filter: Online / Walk-in
- Date picker
- Search (patient name, phone, complaints)

**Sorting:**
- By appointment date
- By request date (created_at)

**Actions:**
- View details (leads to AppointmentShow)
- Register walk-in button

### 3. Appointment Show (`app/Livewire/Nurse/AppointmentShow.php`)

**Displays:**
- Patient information (name, DOB, age, gender, phone, address)
- Account owner information
- Appointment details (date, time, consultation type)
- Chief complaints
- Queue number (if approved)

**Actions:**
- **Approve** (pending only)
  - Optional appointment time
  - Optional notes
  - Generates queue number automatically
  - Notifies patient
- **Cancel** (pending/approved)
  - Requires reason (min 10 characters)
  - Cancels associated queue
  - Notifies patient

### 4. Walk-in Registration (`app/Livewire/Nurse/WalkInRegistration.php`)

**Form fields:**
- Consultation type (required)
- Patient info: First name, middle name, last name, DOB, gender, phone
- Address: Province, municipality, barangay, street
- Chief complaints (required)

**Behavior:**
- Creates appointment with `status: pending`, `source: walk-in`
- Redirects to appointment show page for approval
- Same approval workflow as online bookings

### 5. Today's Queue (`app/Livewire/Nurse/TodayQueue.php`)

**Displays:**
- Currently serving patients
- Pending check-ins (approved appointments for today)
- Queue table with status tabs

**Status tabs:**
- Waiting
- Called
- Serving
- Completed
- All

**Queue Actions:**

| Current Status | Available Actions |
|----------------|-------------------|
| Waiting | Call, Serve |
| Called | Serve, Skip |
| Serving | Record Vitals, Forward to Doctor |
| Skipped | Requeue |
| Completed | - (shows "Forwarded") |

**Check-in Modal:**
- Confirms patient arrival
- Updates appointment status to `checked_in`

**Vital Signs Modal:**
- Common: Temperature, BP, Cardiac Rate, Respiratory Rate, Weight, Height
- Pediatric: Head Circumference, Chest Circumference
- OB: Fetal Heart Tone, Fundal Height, Last Menstrual Period
- Updated chief complaints

**Forward to Doctor:**
- Requires vital signs to be recorded first
- Updates queue status to `completed`
- Notifies doctors of the consultation type

## Workflow

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
Nurse records vital signs
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
Nurse records vital signs
    ↓
Nurse forwards to doctor → Doctors notified
```

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

## Medical Record Creation

Medical records are created when nurse starts serving a patient:
- Copies patient info from appointment
- Sets `visit_date` to today
- Sets `time_in` to current time
- Sets `status` to `in_progress`
- Links to queue and appointment

## Notifications

| Event | Recipients | Message |
|-------|------------|---------|
| Appointment approved | Patient | Queue number assigned |
| Appointment cancelled | Patient | Cancellation reason |
| Patient called | Patient | Proceed to nurse station |
| Patient ready for doctor | Doctors (of consultation type) | Patient is ready |

## Database Tables Used

- `appointments` - Patient appointment requests
- `queues` - Queue entries for today
- `medical_records` - Patient visit records
- `consultation_types` - Types of consultations
- `users` - Nurses, doctors, patients

## Files

### Livewire Components
- `app/Livewire/Nurse/Dashboard.php`
- `app/Livewire/Nurse/Appointments.php`
- `app/Livewire/Nurse/AppointmentShow.php`
- `app/Livewire/Nurse/TodayQueue.php`
- `app/Livewire/Nurse/WalkInRegistration.php`

### Blade Views
- `resources/views/livewire/nurse/dashboard.blade.php`
- `resources/views/livewire/nurse/appointments.blade.php`
- `resources/views/livewire/nurse/appointment-show.blade.php`
- `resources/views/livewire/nurse/today-queue.blade.php`
- `resources/views/livewire/nurse/walk-in-registration.blade.php`

## Design Notes

- Minimal color scheme (black, white, gray/zinc)
- No colorful badges or status indicators
- Clean, professional appearance
- Dark mode support
- Responsive design
