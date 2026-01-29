# System Logic Overview

## Documentation Structure

This documentation explains the deep-level logic of how each module works, including:
- Tables and columns used
- Relationships between entities
- Practical usage examples
- Form mappings and workflows

### Module Documentation

| File | Module | Description |
|------|--------|-------------|
| [LOGIC-PATIENT.md](./LOGIC-PATIENT.md) | Patient Portal | Booking, queue tracking, medical records |
| [LOGIC-NURSE.md](./LOGIC-NURSE.md) | Nurse Station | Appointments, triage, queue management |
| [LOGIC-DOCTOR.md](./LOGIC-DOCTOR.md) | Doctor Station | Examination, diagnosis, prescriptions |
| [LOGIC-CASHIER.md](./LOGIC-CASHIER.md) | Cashier Desk | Billing, payments, discounts |
| [LOGIC-ADMIN.md](./LOGIC-ADMIN.md) | Admin Console | User management, services, drugs |

---

## Core Concept: Self-Contained Patient Data

### Why Patient Data is Duplicated

In traditional systems, patient information is referenced:
```
medical_record.patient_id → patients.id → patient info
```

In HQMS, patient data is **copied** into each record:
```
medical_record contains: patient_first_name, patient_last_name, etc.
```

### Reasons:

1. **Dependent Booking** - Parent (account owner) can book for children
   ```
   User: Maria Santos (mother)
   Books appointment for: Juan Santos Jr. (child)

   appointments.user_id = Maria's ID (for notifications)
   appointments.patient_first_name = "Juan"
   appointments.relationship_to_account = "child"
   ```

2. **Historical Accuracy** - Patient info at time of visit is preserved
   ```
   Visit 2024: Patient address = "123 Main St"
   Visit 2026: Patient address = "456 Oak Ave"

   Each record shows address at that visit
   ```

3. **Self-Contained Records** - Each record is complete without joins
   ```php
   $record->patient_full_name  // Works without loading user
   $record->patient_age        // Calculated from stored DOB
   ```

---

## User Roles and Access

### Role Assignment (Spatie Permission)

```php
// Creating user with role
$user = User::create([...]);
$user->assignRole('patient');  // or 'nurse', 'doctor', 'cashier', 'admin'

// Checking role
$user->hasRole('doctor');      // true/false
$user->isDoctor();             // Helper method
```

### Role Capabilities

| Role | Can Access | Primary Actions |
|------|------------|-----------------|
| Patient | Patient Portal | Book appointments, view records |
| Nurse | Nurse Station | Manage queue, triage, record vitals |
| Doctor | Doctor Station | Examine, diagnose, prescribe |
| Cashier | Cashier Desk | Process billing, receive payments |
| Admin | Admin Console | Manage users, services, settings |

---

## Data Flow Summary

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           DATA FLOW                                          │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  PATIENT BOOKS                                                              │
│  ─────────────                                                              │
│  appointments                                                               │
│    ├─ user_id (account owner - receives notifications)                     │
│    ├─ patient_* fields (actual patient info - may differ)                  │
│    ├─ consultation_type_id                                                 │
│    └─ status: pending                                                      │
│                                                                             │
│         │                                                                   │
│         ▼                                                                   │
│  NURSE APPROVES & CHECKS IN                                                │
│  ─────────────────────────                                                 │
│  appointments.status → approved → checked_in                               │
│  queues (created)                                                          │
│    ├─ appointment_id                                                       │
│    ├─ queue_number (auto-generated per consultation type)                  │
│    └─ status: waiting                                                      │
│                                                                             │
│         │                                                                   │
│         ▼                                                                   │
│  NURSE CALLS & TRIAGES                                                     │
│  ─────────────────────                                                     │
│  queues.status → called → serving                                          │
│  medical_records (created)                                                 │
│    ├─ queue_id                                                             │
│    ├─ patient_* (copied from appointment)                                  │
│    ├─ vital signs (recorded by nurse)                                      │
│    └─ status: in_progress                                                  │
│                                                                             │
│         │                                                                   │
│         ▼                                                                   │
│  NURSE FORWARDS TO DOCTOR                                                  │
│  ────────────────────────                                                  │
│  queues.status → waiting (for doctor)                                      │
│  queues.doctor_id = assigned doctor                                        │
│                                                                             │
│         │                                                                   │
│         ▼                                                                   │
│  DOCTOR EXAMINES                                                           │
│  ───────────────                                                           │
│  medical_records                                                           │
│    ├─ diagnosis, plan, pertinent_hpi_pe                                   │
│    ├─ examined_at, examination_ended_at                                   │
│    └─ status: for_billing                                                 │
│  prescriptions (created)                                                   │
│    ├─ medication_name, dosage, frequency                                  │
│    └─ hospital_drug_id (if from inventory)                                │
│  queues.status → completed                                                 │
│                                                                             │
│         │                                                                   │
│         ▼                                                                   │
│  CASHIER PROCESSES BILLING                                                 │
│  ────────────────────────                                                  │
│  billing_transactions (created)                                            │
│    ├─ medical_record_id                                                   │
│    ├─ subtotal, discount, total_amount                                    │
│    └─ status: paid                                                        │
│  billing_items (created)                                                   │
│    ├─ consultation fee                                                    │
│    ├─ prescriptions (hospital drugs)                                      │
│    └─ additional services                                                 │
│  medical_records.status → completed                                        │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Status Enums Reference

### Appointment Status

| Status | Description | Set By |
|--------|-------------|--------|
| `pending` | Awaiting approval | System (on create) |
| `approved` | Approved, awaiting check-in | Nurse |
| `checked_in` | Patient arrived, in queue | Nurse |
| `in_progress` | Being served | System |
| `completed` | Visit finished | System |
| `cancelled` | Cancelled by patient/staff | Patient/Nurse |
| `no_show` | Patient didn't arrive | Nurse |

### Queue Status

| Status | Description | Set By |
|--------|-------------|--------|
| `waiting` | In queue, not yet called | System (on create) |
| `called` | Number displayed on screen | Nurse |
| `serving` | With nurse (triage) | Nurse |
| `completed` | Visit finished | System |
| `skipped` | Patient didn't respond | Nurse |
| `cancelled` | Removed from queue | Nurse |

### Medical Record Status

| Status | Description | Set By |
|--------|-------------|--------|
| `in_progress` | Being filled (triage/exam) | System |
| `for_billing` | Ready for payment | Doctor |
| `for_admission` | Needs hospital admission | Doctor |
| `completed` | Fully processed | Cashier |

### Billing Transaction Status

| Status | Description | Set By |
|--------|-------------|--------|
| `pending` | Awaiting payment | System |
| `paid` | Fully paid | Cashier |
| `partial` | Partial payment | Cashier |
| `cancelled` | Transaction voided | Cashier |

---

## Timestamps and Tracking

### Appointment Tracking

```php
$appointment->created_at      // When booked
$appointment->approved_at     // When nurse approved
$appointment->checked_in_at   // When patient arrived
$appointment->approved_by     // Nurse who approved (user_id)
```

### Queue Tracking

```php
$queue->created_at           // When added to queue
$queue->called_at            // When number called
$queue->serving_started_at   // When triage started
$queue->serving_ended_at     // When completed
$queue->served_by            // Nurse who served (user_id)
```

### Medical Record Tracking

```php
$record->time_in                  // Patient arrival
$record->vital_signs_recorded_at  // Vitals taken
$record->examined_at              // Doctor started exam
$record->examination_ended_at     // Doctor finished
```

### Billing Tracking

```php
$billing->created_at    // When bill created
$billing->paid_at       // When payment received
$billing->processed_by  // Cashier who processed (user_id)
```
