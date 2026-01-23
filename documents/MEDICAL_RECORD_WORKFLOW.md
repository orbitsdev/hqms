# Medical Record Workflow

This document describes the medical record creation process and its flow to billing or admission.

## Data Model

```
medical_records (main visit record)
├── record_number: MR-2026-00001 (auto-generated)
├── Patient info (self-contained snapshot from appointment)
├── Companion/watcher info (for minors)
├── Visit timing and type
├── Vital signs (nurse input)
├── Diagnosis and plan (doctor input)
├── Doctor's recommendations (discount, etc.)
└── Status: in_progress → for_billing OR for_admission → completed
    │
    ├─→ prescriptions (medications prescribed)
    ├─→ billing_transactions (if for_billing)
    └─→ admissions (if for_admission)
```

---

## Medical Record Fields

### Record Identification
| Field | Type | Description |
|-------|------|-------------|
| `record_number` | string(20) | Auto-generated: MR-2026-00001 |

### Relations
| Field | Type | Description |
|-------|------|-------------|
| `user_id` | foreignId | Account owner (for access control) |
| `consultation_type_id` | foreignId | OB, Pedia, General |
| `appointment_id` | foreignId | Linked appointment (nullable) |
| `queue_id` | foreignId | Linked queue (nullable) |
| `doctor_id` | foreignId | Attending doctor |
| `nurse_id` | foreignId | Recording nurse |

### Patient Personal Information (Self-Contained)
| Field | Type | Description |
|-------|------|-------------|
| `patient_first_name` | string | Patient's first name |
| `patient_middle_name` | string | Patient's middle name |
| `patient_last_name` | string | Patient's last name |
| `patient_date_of_birth` | date | For age calculation |
| `patient_gender` | enum | male / female |
| `patient_marital_status` | enum | child / single / married / widow |
| `patient_province` | string | Address |
| `patient_municipality` | string | Address |
| `patient_barangay` | string | Address |
| `patient_street` | text | Address |
| `patient_contact_number` | string(20) | Phone |
| `patient_occupation` | string | Job |
| `patient_religion` | string | Religion |

### Companion/Watcher (for minors or assisted patients)
| Field | Type | Description |
|-------|------|-------------|
| `companion_name` | string | Watcher's name |
| `companion_contact` | string(20) | Watcher's phone |
| `companion_relationship` | string | Relationship to patient |

### Patient Medical Background
| Field | Type | Description |
|-------|------|-------------|
| `patient_blood_type` | enum | A+, A-, B+, B-, AB+, AB-, O+, O- |
| `patient_allergies` | text | Known allergies |
| `patient_chronic_conditions` | text | Existing conditions |

### Emergency Contact
| Field | Type | Description |
|-------|------|-------------|
| `emergency_contact_name` | string | Emergency contact name |
| `emergency_contact_phone` | string(20) | Emergency contact phone |

### Visit Information
| Field | Type | Description |
|-------|------|-------------|
| `visit_date` | date | Date of visit |
| `time_in` | timestamp | When patient arrived |
| `time_in_period` | enum | am / pm |
| `visit_type` | enum | new / old / revisit |
| `service_type` | enum | checkup / admission |
| `ob_type` | enum | prenatal / post-natal (OB only) |
| `service_category` | enum | surgical / non-surgical |

### Chief Complaints
| Field | Type | Description |
|-------|------|-------------|
| `chief_complaints_initial` | text | From appointment |
| `chief_complaints_updated` | text | Updated during visit |

### Vital Signs (Nurse Input)
| Field | Type | Description |
|-------|------|-------------|
| `temperature` | decimal(4,1) | Body temperature (°C) |
| `blood_pressure` | string(20) | BP reading (e.g., "120/80") |
| `cardiac_rate` | integer | Heart rate (bpm) |
| `respiratory_rate` | integer | Breathing rate (bpm) |
| `weight` | decimal(5,2) | Weight (kg) |
| `height` | decimal(5,2) | Height (cm) |
| `head_circumference` | decimal(5,2) | Head circ (cm) - Pedia |
| `chest_circumference` | decimal(5,2) | Chest circ (cm) - Pedia |
| `fetal_heart_tone` | integer | FHT (bpm) - OB |
| `fundal_height` | decimal(5,2) | FH (cm) - OB |
| `last_menstrual_period` | date | LMP - OB |
| `vital_signs_recorded_at` | timestamp | When vitals were taken |

### Diagnosis (Doctor Input)
| Field | Type | Description |
|-------|------|-------------|
| `pertinent_hpi_pe` | text | History & Physical Exam |
| `diagnosis` | text | Doctor's diagnosis |
| `plan` | text | Treatment plan |
| `procedures_done` | text | Procedures performed |
| `prescription_notes` | text | General prescription notes |

### Examination Timing
| Field | Type | Description |
|-------|------|-------------|
| `examined_at` | timestamp | When exam started |
| `examination_ended_at` | timestamp | When exam ended |
| `examination_time` | enum | am / pm |

### Doctor's Recommendations (for billing reference)
| Field | Type | Description |
|-------|------|-------------|
| `suggested_discount_type` | enum | none / family / senior / pwd / employee / other |
| `suggested_discount_reason` | text | Reason for discount |

### Status
| Field | Type | Description |
|-------|------|-------------|
| `status` | enum | in_progress / for_billing / for_admission / completed |
| `notes` | text | General notes |

---

## Workflow: Medical Record Creation to Completion

### Step 1: Patient Arrives (from Queue)

```
Queue status: 'serving'
     │
     ▼
Medical Record Created:
├── record_number: auto-generated (MR-2026-00001)
├── Patient info: copied from appointment
├── Companion info: if applicable (minors)
├── time_in: NOW
├── visit_date: TODAY
├── visit_type: new / old / revisit
├── service_type: checkup / admission
├── status: 'in_progress'
└── chief_complaints_initial: from appointment
```

### Step 2: Nurse Records Vital Signs

```
Nurse fills in vital signs based on consultation type:

ALL TYPES:
├── temperature
├── blood_pressure
├── cardiac_rate
├── respiratory_rate

PEDIA / GENERAL:
├── weight
├── height
├── head_circumference (Pedia)
├── chest_circumference (Pedia)

OB:
├── fetal_heart_tone
├── fundal_height
├── last_menstrual_period
├── ob_type: prenatal / post-natal

vital_signs_recorded_at: NOW
```

### Step 3: Doctor Examines and Diagnoses

```
Doctor fills in:
├── examined_at: NOW
├── pertinent_hpi_pe: findings
├── diagnosis: what's wrong
├── plan: treatment plan
├── procedures_done: if any
├── examination_ended_at: NOW
│
├── PRESCRIPTIONS (separate table):
│   └── Doctor adds medications
│       └── Can mark hospital_drug_id if available
│
└── RECOMMENDATIONS:
    ├── suggested_discount_type: senior / pwd / family / etc.
    └── suggested_discount_reason: "Senior citizen ID #..."
```

### Step 4: Decision Point - Billing or Admission

```
                    Doctor Decides
                          │
          ┌───────────────┴───────────────┐
          ▼                               ▼
┌─────────────────────┐       ┌─────────────────────┐
│   NORMAL (Billing)  │       │     ADMISSION       │
│                     │       │                     │
│ status='for_billing'│       │ status='for_admission'
└─────────────────────┘       └─────────────────────┘
          │                               │
          ▼                               ▼
┌─────────────────────┐       ┌─────────────────────┐
│ billing_transactions│       │    admissions       │
│ ─────────────────── │       │ ─────────────────── │
│ medical_record_id   │       │ medical_record_id   │
│ discount from doctor│       │ admission_date      │
│ suggestion          │       │ room/bed assignment │
│ received_in_billing │       │ status: 'active'    │
│ payment processing  │       │                     │
│ ended_in_billing    │       │ (No billing here -  │
│                     │       │  separate process)  │
└─────────────────────┘       └─────────────────────┘
          │                               │
          ▼                               ▼
┌─────────────────────┐       ┌─────────────────────┐
│ medical_record      │       │ Later: discharge    │
│ status='completed'  │       │ → then billing      │
└─────────────────────┘       └─────────────────────┘
```

---

## Status Flow

```
in_progress ─────┬─────→ for_billing ────→ completed
                 │
                 └─────→ for_admission ──→ (handled by admissions)
```

| Status | Description |
|--------|-------------|
| `in_progress` | Record created, nurse/doctor working on it |
| `for_billing` | Doctor done, ready for cashier |
| `for_admission` | Patient needs to be admitted |
| `completed` | Billing done, record finalized |

---

## Related Tables

### prescriptions
```
medical_record_id → medical_records.id
prescribed_by → users.id (doctor)
medication_name
dosage, frequency, duration
instructions, quantity
hospital_drug_id → hospital_drugs.id (if from inventory)
is_hospital_drug: boolean
```

### billing_transactions
```
medical_record_id → medical_records.id
user_id → users.id (patient/account owner)
transaction_number: auto-generated
transaction_date
discount_type: from doctor's suggestion or cashier override
discount_amount, discount_reason
total_amount, amount_paid, balance
payment_status: pending / partial / paid / cancelled
received_in_billing_at, ended_in_billing_at
processed_by → users.id (cashier)
```

### admissions
```
medical_record_id → medical_records.id
user_id → users.id (patient)
admitted_by → users.id (staff)
admission_number: auto-generated
admission_date, discharge_date
room_number, bed_number
reason_for_admission
discharge_summary
status: active / discharged
```

---

## Form Field Mapping

Based on actual hospital forms (patient.jpg, pedia.jpg, onfield.jpg):

| Form Field | Database Field |
|------------|----------------|
| Case No. | `record_number` |
| Date | `visit_date` |
| Time In | `time_in` |
| AM/PM | `time_in_period` |
| OLD/NEW/REVISIT | `visit_type` |
| Check-up/Admission | `service_type` |
| PEDIA/GENERAL/OB/GYNE | `consultation_type_id` |
| PRENATAL/POST-NATAL | `ob_type` |
| SURGICAL/NON-SURGICAL | `service_category` |
| Name | `patient_first_name`, `patient_middle_name`, `patient_last_name` |
| Address | `patient_province`, `patient_municipality`, `patient_barangay`, `patient_street` |
| Age/Sex | calculated from `patient_date_of_birth` + `patient_gender` |
| Birthdate | `patient_date_of_birth` |
| Status | `patient_marital_status` |
| Contact No. | `patient_contact_number` |
| Occupation | `patient_occupation` |
| Religion | `patient_religion` |
| Temp | `temperature` |
| CR | `cardiac_rate` |
| RR | `respiratory_rate` |
| BP | `blood_pressure` |
| Weight | `weight` |
| Height | `height` |
| Head Circ | `head_circumference` |
| Chest Circ | `chest_circumference` |
| FHT | `fetal_heart_tone` |
| FH | `fundal_height` |
| LMP | `last_menstrual_period` |
| Time Examined by Physician | `examined_at` |
| Time Examination Ended | `examination_ended_at` |
| AM/PM | `examination_time` |
| Chief Complaint | `chief_complaints_initial`, `chief_complaints_updated` |
| Pertinent HPI/PE | `pertinent_hpi_pe` |
| Diagnosis | `diagnosis` |
| Plan | `plan` |
| Procedure Done | `procedures_done` |

---

## Record Number Generation

Format: `MR-{YEAR}-{SEQUENCE}`

Example: `MR-2026-00001`

Application logic:
```php
// Get last record number for current year
$lastRecord = MedicalRecord::whereYear('created_at', now()->year)
    ->orderBy('id', 'desc')
    ->first();

$sequence = $lastRecord
    ? intval(substr($lastRecord->record_number, -5)) + 1
    : 1;

$recordNumber = sprintf('MR-%d-%05d', now()->year, $sequence);
```
