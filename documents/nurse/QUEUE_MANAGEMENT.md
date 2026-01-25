# Nurse Queue Management

This document explains how the queue management system works for nurses.

## Queue Status Flow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           QUEUE STATUS FLOW                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   WAITING ───[Call]───► CALLED ───[Serve]───► SERVING ───[Forward]───► COMPLETED
│      │                     │                      │
│      │                     │                      │
│      └────[Skip]────► SKIPPED ◄────[Skip]─────────┘
│                           │                       │
│                           │                       │
│                     [Requeue]                  [Stop]
│                           │                       │
│                           └───────────────────────┘
│                                     │
│                                     ▼
│                                  WAITING
│
└─────────────────────────────────────────────────────────────────────────────┘
```

## Queue Statuses

| Status | Description |
|--------|-------------|
| **waiting** | Patient is in the waiting area, ready to be called |
| **called** | Patient's number has been announced, waiting for them to come to station |
| **serving** | Nurse is currently taking vital signs for this patient |
| **skipped** | Patient didn't respond when called (can be requeued later) |
| **completed** | Vital signs done, patient forwarded to doctor |

## Actions Explained

### 1. Call Patient

**Purpose:** Announce the patient's queue number so they know it's their turn.

**What happens:**
- Status changes: `waiting` → `called`
- Records `called_at` timestamp
- Sends push notification to patient's phone/app
- Queue number appears on the public display screen (TV in waiting area)

**When to use:**
- When ready to serve the next patient
- Gives patient time to walk to the nurse station

---

### 2. Serve Patient

**Purpose:** Start taking the patient's vital signs.

**What happens:**
- Status changes: `waiting` or `called` → `serving`
- Records `serving_started_at` timestamp
- Assigns the nurse (`served_by`) to the queue
- Creates a new medical record if one doesn't exist
- Appointment status changes to `in_progress`

**When to use:**
- When patient arrives at the nurse station
- Can skip "Call" and directly serve if patient is already present

---

### 3. Skip Patient

**Purpose:** Move patient aside when they don't respond to their call.

**What happens:**
- Status changes: `waiting` or `called` → `skipped`
- Patient is removed from active queue
- Patient appears in "Skipped" tab for later action

**When to use:**
- Patient was called but didn't come
- Patient stepped out temporarily
- Need to serve other patients first

**Note:** Shows confirmation modal before skipping.

---

### 4. Requeue Patient

**Purpose:** Return a skipped patient back to the waiting queue.

**What happens:**
- Status changes: `skipped` → `waiting`
- Clears `called_at` timestamp
- **Queue number stays the same** (P-003 remains P-003)
- Patient can be called/served again

**When to use:**
- Skipped patient has returned
- Patient was accidentally skipped

**Note:** Shows confirmation modal. Patient keeps their original queue number.

---

### 5. Stop Serving

**Purpose:** Cancel serving if wrong patient was selected.

**What happens:**
- Status changes: `serving` → `waiting`
- Clears `serving_started_at` and `served_by`
- Appointment status reverts to `checked_in`
- Medical record is deleted (only if no vital signs were recorded)
- If vital signs were already saved, medical record is preserved

**When to use:**
- Accidentally selected wrong patient
- Patient needs to step out urgently
- Need to prioritize emergency patient

**Note:** Shows confirmation modal before stopping.

---

### 6. Record Vital Signs

**Purpose:** Record patient's vital signs into the medical record.

**What happens:**
- Opens vital signs modal
- Saves vital signs to medical record
- Records `vital_signs_recorded_at` timestamp
- Enables "Forward" button

**Vital signs recorded:**
- **Common:** Temperature, Blood Pressure, Cardiac Rate, Respiratory Rate, Weight, Height
- **Pediatric:** Head Circumference, Chest Circumference
- **OB/GYN:** Fetal Heart Tone, Fundal Height, Last Menstrual Period

---

### 7. Forward to Doctor

**Purpose:** Complete nurse assessment and send patient to doctor.

**What happens:**
- Status changes: `serving` → `completed`
- Records `serving_ended_at` timestamp
- Medical record status changes to `in_progress`
- Notifies doctors of the relevant consultation type
- Queue number removed from public display

**Requirements:**
- Vital signs must be recorded first

---

## Queue Display (Waiting Area TV)

The public queue display shows patients their queue status:

```
┌─────────────────────────────────────────────────────────────┐
│                      PEDIATRICS                              │
│                                                              │
│      NOW SERVING          │         NOW CALLING              │
│         P-003             │            P-004                 │
│                           │         (Flashing + Sound)       │
│  ─────────────────────────────────────────────────────────── │
│                                                              │
│  NEXT IN LINE:  P-005  →  P-006  →  P-007  →  P-008         │
│                                                              │
│                     Total Waiting: 4                         │
└─────────────────────────────────────────────────────────────┘
```

- **Now Serving:** Current patient being attended
- **Now Calling:** Patient should come now (with sound alert)
- **Next in Line:** Upcoming queue numbers so patients can prepare

---

## Consultation Type Tabs

Queues are organized by consultation type:
- **All Queues:** View all types together
- **O (Obstetrics):** Pregnancy-related consultations
- **P (Pediatrics):** Children's consultations
- **G (General):** General consultations

Each type has independent queue numbers (O-001, P-001, G-001).

---

## Priority Levels

| Priority | Color | Description |
|----------|-------|-------------|
| **Emergency** | Red | Life-threatening, serve immediately |
| **Urgent** | Amber | Needs attention soon |
| **Normal** | Gray | Standard queue order |

Patients are automatically sorted: Emergency → Urgent → Normal, then by queue number.

---

## Search

Use the search box to find patients by:
- Queue number (e.g., "P-003")
- Patient first name
- Patient last name

---

## Check-in Flow

Before a patient can be in the queue, they must check in:

1. Patient books appointment online or walk-in
2. Nurse approves appointment → Queue number assigned
3. Patient arrives at clinic
4. Nurse confirms check-in (updates `checked_in_at`)
5. Patient appears in "Waiting" queue

---

## Database Fields Updated

| Action | Fields Updated |
|--------|----------------|
| **Call** | `status`, `called_at` |
| **Serve** | `status`, `serving_started_at`, `served_by` |
| **Skip** | `status` |
| **Requeue** | `status`, `called_at` (cleared) |
| **Stop** | `status`, `serving_started_at`, `served_by`, `called_at` (all cleared) |
| **Forward** | `status`, `serving_ended_at` |

---

## Related Files

- **Component:** `app/Livewire/Nurse/TodayQueue.php`
- **View:** `resources/views/livewire/nurse/today-queue.blade.php`
- **Model:** `app/Models/Queue.php`
- **Tests:** `tests/Feature/Nurse/TodayQueueTest.php`
