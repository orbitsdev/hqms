# Appointment & Queue Workflow

This document describes the complete workflow from booking to queue completion for both online and walk-in patients.

## Data Model Overview

```
users (auth + identity)
  - first_name, last_name, middle_name (for search/identification)
  - email, phone, password (auth)
  └─> personal_information (extended details: dob, address, emergency contact)
  └─> appointments (patient info captured per appointment)
  └─> medical_records (copies patient info from appointment)
```

**Key Concept:**
- `user_id` = Account owner (patient themselves OR parent)
- `appointments` contains **patient information** (who the visit is actually for)
- Medical records copy patient info from appointments (self-contained)

---

## Flow 1: Online Booking

### Step 1: Login/Register

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ PATIENT/PARENT (Online)                                                     │
│                                                                             │
│ Requirements:                                                               │
│ - Email + Password (required for online accounts)                           │
│ - Complete personal_information                                             │
│ - Phone number (optional but recommended)                                   │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Step 2: Book Appointment

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ BOOK APPOINTMENT                                                            │
│                                                                             │
│ 1. Select consultation_type (OB, Pedia, General)                            │
│ 2. View available dates (from doctor_schedules)                             │
│    - Dates with no doctor available are shown as unavailable                │
│ 3. Select appointment date                                                  │
│ 4. Enter PATIENT information:                                               │
│    - Patient name (first, middle, last)                                     │
│    - Date of birth                                                          │
│    - Gender                                                                 │
│    - Phone (optional)                                                       │
│    - Address (optional)                                                     │
│    - Relationship to account (self, child, spouse, parent, sibling, other)  │
│ 5. Enter chief complaints                                                   │
│ 6. Submit                                                                   │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Step 3: Appointment Created

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ appointments table                                                          │
│ ─────────────────────────────────────────────────────────────────────────── │
│ user_id: account owner (for notifications)                                  │
│ consultation_type_id: selected                                              │
│ doctor_id: null (assigned later if needed)                                  │
│                                                                             │
│ PATIENT INFO:                                                               │
│ patient_first_name, patient_middle_name, patient_last_name                  │
│ patient_date_of_birth, patient_gender                                       │
│ patient_phone, patient_province, patient_municipality                       │
│ patient_barangay, patient_street                                            │
│ relationship_to_account: 'self' / 'child' / etc.                            │
│                                                                             │
│ appointment_date: selected                                                  │
│ chief_complaints: entered                                                   │
│ status: 'pending'                                                           │
│ source: 'online'                                                            │
│ created_at: NOW (used for queue ordering)                                   │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Step 4: Nurse Reviews & Approves

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ NURSE DASHBOARD                                                             │
│                                                                             │
│ Nurse can see:                                                              │
│ - Account owner name (for contact)                                          │
│ - Patient name, age, gender (who the visit is for)                          │
│ - Relationship (self, child, etc.)                                          │
│ - Consultation type                                                         │
│ - Appointment date                                                          │
│ - Chief complaints                                                          │
│                                                                             │
│ Actions:                                                                    │
│ - APPROVE: Generates queue number                                           │
│ - DECLINE: With reason + suggested alternative date                         │
└─────────────────────────────────────────────────────────────────────────────┘
```

**If Declined:**
- Patient receives notification with:
  - Decline reason
  - Suggested alternative date
- Patient can rebook

**If Approved:**

### Step 5: Queue Generated

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ queues table                                                                │
│ ─────────────────────────────────────────────────────────────────────────── │
│ appointment_id: linked                                                      │
│ user_id: account owner                                                      │
│ consultation_type_id: from appointment                                      │
│ doctor_id: assigned (if any)                                                │
│                                                                             │
│ queue_number: assigned based on approval order                              │
│ queue_date: appointment_date                                                │
│ session_number: 1 (default, increments on reset)                            │
│                                                                             │
│ status: 'waiting'                                                           │
│ source: 'online'                                                            │
│ priority: 'normal' (can be changed to urgent/emergency)                     │
└─────────────────────────────────────────────────────────────────────────────┘
```

**Queue Number Logic:**
```sql
SELECT COUNT(*) + 1 FROM queues
WHERE queue_date = ?
  AND consultation_type_id = ?
  AND session_number = ?
```

### Step 6: Patient Notified

- Account owner receives notification:
  - Queue number (e.g., "O-005" for OB queue #5)
  - Appointment date
  - Estimated time (optional)

### Step 7: Appointment Day - Check In

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ CHECK IN                                                                    │
│                                                                             │
│ - Patient arrives at clinic                                                 │
│ - Nurse/Kiosk marks as checked in                                           │
│                                                                             │
│ Updates:                                                                    │
│ - appointment.status → 'checked_in'                                         │
│ - appointment.checked_in_at → NOW                                           │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Flow 2: Walk-in

### Step 1: Patient Arrives

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ PATIENT ARRIVES (Walk-in)                                                   │
│                                                                             │
│ No prior booking. Patient comes directly to clinic.                         │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Step 2: Nurse Checks Existing Account

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ CHECK EXISTING ACCOUNT                                                      │
│                                                                             │
│ Search by:                                                                  │
│ - Phone number                                                              │
│ - Name (first_name, last_name)                                              │
│                                                                             │
│ If FOUND:                                                                   │
│ - Use existing user account                                                 │
│                                                                             │
│ If NOT FOUND:                                                               │
│ - Create new account (optional)                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐   │
│   │ users                                                               │   │
│   │ ───────────────────────────────────────────────────────────────     │   │
│   │ first_name: entered                                                 │   │
│   │ middle_name: entered (optional)                                     │   │
│   │ last_name: entered                                                  │   │
│   │ phone: entered                                                      │   │
│   │ email: NULL (walk-in)                                               │   │
│   │ password: NULL (walk-in)                                            │   │
│   └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐   │
│   │ personal_information                                                │   │
│   │ ───────────────────────────────────────────────────────────────     │   │
│   │ date_of_birth, gender, address, etc. (as available)                 │   │
│   └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│ Note: Walk-in user can later "upgrade" account by adding email/password     │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Step 3: Nurse Creates Appointment

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ NURSE CREATES APPOINTMENT                                                   │
│                                                                             │
│ Same form as online, but nurse fills it:                                    │
│ - Select consultation type                                                  │
│ - Enter patient information (may differ from account owner)                 │
│ - Enter chief complaints                                                    │
│                                                                             │
│ appointments table:                                                         │
│ - user_id: patient account (existing or newly created)                      │
│ - patient_*: actual patient details                                         │
│ - appointment_date: TODAY                                                   │
│ - status: 'pending'                                                         │
│ - source: 'walk-in'                                                         │
│ - created_at: NOW                                                           │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Step 4: Nurse Approves Immediately

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ IMMEDIATE APPROVAL                                                          │
│                                                                             │
│ - appointment.status → 'approved'                                           │
│ - appointment.approved_by → nurse_id                                        │
│ - appointment.approved_at → NOW                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Step 5: Queue Generated

Same as online flow - queue number assigned based on approval order.

Walk-in gets NEXT available number (first-come-first-served with online bookings).

### Step 6: Patient Receives Queue Slip

- Printed slip or shown on screen
- Contains queue number and estimated wait time

### Step 7: Mark as Checked-In

Since patient is already at clinic:
- appointment.status → 'checked_in'
- appointment.checked_in_at → NOW

---

## Flow 3: Service & Completion (Shared)

### Queue Display

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ QUEUE DISPLAY / WAITING AREA                                                │
│                                                                             │
│ ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐               │
│ │   OB Queue      │  │  Pedia Queue    │  │ General Queue   │               │
│ │   Now: O-005    │  │  Now: P-003     │  │ Now: G-012      │               │
│ │   Next: O-006   │  │  Next: P-004    │  │ Next: G-013     │               │
│ └─────────────────┘  └─────────────────┘  └─────────────────┘               │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Doctor/Nurse Calls Patient

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ CALL PATIENT                                                                │
│                                                                             │
│ - queue.status → 'called'                                                   │
│ - queue.called_at → NOW                                                     │
│ - Display updates                                                           │
│ - Announcement plays (optional)                                             │
│                                                                             │
│ If patient doesn't respond:                                                 │
│ - Can be skipped: queue.status → 'skipped'                                  │
│ - Can be re-called later                                                    │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Consultation Starts

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ CONSULTATION STARTS                                                         │
│                                                                             │
│ - queue.status → 'serving'                                                  │
│ - queue.serving_started_at → NOW                                            │
│ - appointment.status → 'in_progress'                                        │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Medical Record Created

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ MEDICAL RECORD CREATED                                                      │
│                                                                             │
│ medical_records table:                                                      │
│ - appointment_id / queue_id: linked                                         │
│ - Patient info COPIED from appointment:                                     │
│   patient_first_name, patient_last_name, patient_date_of_birth, etc.        │
│ - Vital signs (captured during visit)                                       │
│ - Diagnosis                                                                 │
│ - Prescription                                                              │
│ - Notes                                                                     │
│                                                                             │
│ Note: Patient info is self-contained in medical record                      │
│       (snapshot at time of visit)                                           │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Consultation Complete

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ CONSULTATION COMPLETE                                                       │
│                                                                             │
│ - queue.status → 'completed'                                                │
│ - queue.serving_ended_at → NOW                                              │
│ - queue.served_by → doctor_id                                               │
│ - appointment.status → 'completed'                                          │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Post-Visit

- Account owner can view medical record in their account
- If walk-in without email, they can:
  - Add email/password later to access online
  - Request printed copy at clinic

---

## Queue Number Assignment Logic

### Example Scenario

**Date:** January 25 (Today)
**Consultation Type:** OB
**Session:** 1

| # | Source | Booked (created_at) | Approved | Queue # |
|---|--------|---------------------|----------|---------|
| 1 | Online | Jan 20, 10:00 AM | Jan 20, 2:00 PM | O-001 |
| 2 | Online | Jan 21, 9:30 AM | Jan 21, 1:00 PM | O-002 |
| 3 | Online | Jan 23, 3:00 PM | Jan 23, 4:00 PM | O-003 |
| 4 | Walk-in | Jan 25, 8:00 AM | Jan 25, 8:01 AM | O-004 |
| 5 | Walk-in | Jan 25, 8:15 AM | Jan 25, 8:16 AM | O-005 |
| 6 | Online | Jan 24, 11:00 AM | Jan 25, 8:30 AM | O-006 |

**Note:** Queue number is assigned at APPROVAL time, in order of approval for that `queue_date + consultation_type + session`.

---

## Queue Reset

### When to Reset
- All patients served, need fresh start
- Session change (morning → afternoon)
- Manual reset by admin

### Reset Options

| Action | Effect |
|--------|--------|
| Reset specific type | OB session 1 → session 2, queue starts at 1 |
| Reset all types | All types increment session, queues start at 1 |
| New day | Automatic - new queue_date, session = 1 |

### How It Works
```
queues.session_number: 1 → 2

Unique constraint: queue_number + queue_date + consultation_type_id + session_number

Session 1: O-001, O-002, O-003... (historical)
Session 2: O-001, O-002, O-003... (new)
```

---

## Status Flow Summary

### Appointment Status
```
pending → approved → checked_in → in_progress → completed
    ↓         ↓
cancelled  declined (with reason)
    ↓
 no_show
```

### Queue Status
```
waiting → called → serving → completed
            ↓
         skipped (can be re-called)
            ↓
        cancelled
```

---

## Database Tables Involved

| Table | Purpose |
|-------|---------|
| `users` | Account (identity + auth) |
| `personal_information` | Extended account details |
| `consultation_types` | OB, Pedia, General |
| `doctor_schedules` | Doctor availability |
| `appointments` | Booking with patient info |
| `queues` | Queue management |
| `queue_displays` | Display terminals |
| `medical_records` | Visit records (TBD) |
