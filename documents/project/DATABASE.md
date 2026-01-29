# Database Schema Documentation

## Overview

The HQMS database is normalized with 18 core tables. Patient information is intentionally denormalized in `medical_records` and `appointments` to support booking for dependents and preserve historical data.

---

## Entity Relationship Summary

```
users ─────────────────┬──────────────────┬──────────────────┐
   │                   │                  │                  │
   │ 1:1               │ 1:N              │ 1:N              │ 1:N
   ▼                   ▼                  ▼                  ▼
personal_info    appointments        queues          medical_records
                      │                  │                  │
                      │ 1:1              │ 1:1              ├──── prescriptions
                      └──────────────────┴──────────────────┤
                                                           └──── billing_transactions
                                                                       │
                                                                       └── billing_items
```

---

## Core Tables

### 1. users

Authentication and account management.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| first_name | varchar(255) | User's first name |
| middle_name | varchar(255) | Nullable |
| last_name | varchar(255) | User's last name |
| email | varchar(255) | Unique, for login |
| phone | varchar(20) | Contact number |
| password | varchar(255) | Hashed |
| email_verified_at | timestamp | Nullable |
| two_factor_secret | text | 2FA secret (encrypted) |
| two_factor_recovery_codes | text | 2FA backup codes |
| is_active | boolean | Account status |
| deleted_at | timestamp | Soft delete |
| timestamps | | created_at, updated_at |

**Roles** (via Spatie Permission):
- patient
- nurse
- doctor
- cashier
- admin

---

### 2. personal_information

Extended profile for account owner.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK → users |
| first_name | varchar(255) | |
| middle_name | varchar(255) | Nullable |
| last_name | varchar(255) | |
| date_of_birth | date | |
| gender | enum | male, female |
| phone | varchar(20) | |
| province | varchar(255) | |
| municipality | varchar(255) | |
| barangay | varchar(255) | |
| street | text | |
| emergency_contact_name | varchar(255) | |
| emergency_contact_phone | varchar(20) | |
| timestamps | | |

---

### 3. consultation_types

Service categories offered by the clinic.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(255) | e.g., "Pediatrics" |
| short_name | varchar(10) | e.g., "PED" (for queue numbers) |
| description | text | |
| base_consultation_fee | decimal(10,2) | Professional fee |
| is_active | boolean | |
| timestamps | | |

**Examples:**
- Pediatrics (PED) - ₱500.00
- Obstetrics (OB) - ₱600.00
- General Medicine (GEN) - ₱450.00

---

### 4. doctor_schedules

Doctor availability by day and time.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK → users (doctor) |
| consultation_type_id | bigint | FK → consultation_types |
| schedule_type | enum | regular, exception |
| day_of_week | tinyint | 0=Sun, 6=Sat (regular only) |
| date | date | Specific date (exception only) |
| start_time | time | |
| end_time | time | |
| max_patients | int | Per slot |
| is_available | boolean | For exceptions (off days) |
| timestamps | | |

---

### 5. appointments

Patient appointment requests.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK → users (account owner) |
| consultation_type_id | bigint | FK |
| doctor_id | bigint | FK → users, nullable |
| **Patient Info** | | Self-contained |
| patient_first_name | varchar(255) | |
| patient_middle_name | varchar(255) | |
| patient_last_name | varchar(255) | |
| patient_date_of_birth | date | |
| patient_gender | enum | male, female |
| patient_phone | varchar(20) | |
| patient_province | varchar(255) | |
| patient_municipality | varchar(255) | |
| patient_barangay | varchar(255) | |
| patient_street | text | |
| relationship_to_account | enum | self, child, spouse, parent, sibling, other |
| **Appointment Details** | | |
| appointment_date | date | |
| appointment_time | time | Nullable until approved |
| chief_complaints | text | Initial symptoms |
| **Status** | | |
| status | enum | pending, approved, checked_in, in_progress, completed, cancelled, no_show |
| source | enum | online, walk-in |
| approved_by | bigint | FK → users |
| approved_at | timestamp | |
| checked_in_at | timestamp | |
| decline_reason | text | If rejected |
| suggested_date | date | Alternative date |
| timestamps | | |

**Status Flow:**
```
pending → approved → checked_in → in_progress → completed
    ↓         ↓
cancelled   no_show
```

---

### 6. queues

Daily queue entries for patient flow.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| appointment_id | bigint | FK, nullable |
| user_id | bigint | FK → users |
| consultation_type_id | bigint | FK |
| doctor_id | bigint | FK → users, nullable |
| **Queue Info** | | |
| queue_number | int | Daily sequential |
| queue_date | date | |
| session_number | tinyint | AM=1, PM=2 |
| estimated_time | time | |
| priority | enum | normal, urgent, emergency |
| **Status** | | |
| status | enum | waiting, called, serving, completed, skipped, cancelled |
| source | enum | online, walk-in |
| **Tracking** | | |
| called_at | timestamp | When called |
| serving_started_at | timestamp | Nurse started |
| serving_ended_at | timestamp | Completed |
| served_by | bigint | FK → users (nurse) |
| timestamps | | |

**Unique Constraint:**
`queue_number + queue_date + consultation_type_id + session_number`

**Queue Number Format:** `{ConsultationType.short_name}-{queue_number}`
Example: `PED-001`, `OB-015`

---

### 7. medical_records

Complete visit records with self-contained patient data.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| record_number | varchar(20) | Unique, auto: MR-2026-00001 |
| **Relations** | | |
| user_id | bigint | FK → users (account owner) |
| consultation_type_id | bigint | FK |
| appointment_id | bigint | FK, nullable |
| queue_id | bigint | FK, nullable |
| doctor_id | bigint | FK → users |
| nurse_id | bigint | FK → users |
| **Patient Personal Info** | | Self-contained snapshot |
| patient_first_name | varchar(255) | |
| patient_middle_name | varchar(255) | |
| patient_last_name | varchar(255) | |
| patient_date_of_birth | date | |
| patient_gender | enum | male, female |
| patient_marital_status | enum | child, single, married, widow |
| patient_province | varchar(255) | |
| patient_municipality | varchar(255) | |
| patient_barangay | varchar(255) | |
| patient_street | text | |
| patient_contact_number | varchar(20) | |
| patient_occupation | varchar(255) | |
| patient_religion | varchar(255) | |
| patient_blood_type | enum | A+, A-, B+, B-, AB+, AB-, O+, O- |
| patient_allergies | text | |
| patient_chronic_conditions | text | |
| **Companion** | | For minors |
| companion_name | varchar(255) | |
| companion_contact | varchar(20) | |
| companion_relationship | varchar(255) | |
| **Emergency Contact** | | |
| emergency_contact_name | varchar(255) | |
| emergency_contact_phone | varchar(20) | |
| **Visit Info** | | |
| visit_date | date | |
| time_in | timestamp | |
| time_in_period | enum | am, pm |
| visit_type | enum | new, old, revisit |
| service_type | enum | checkup, admission |
| ob_type | enum | prenatal, post-natal (OB only) |
| service_category | enum | surgical, non-surgical |
| **Chief Complaints** | | |
| chief_complaints_initial | text | From appointment |
| chief_complaints_updated | text | Nurse update |
| **Vital Signs** | | Nurse input |
| temperature | decimal(4,1) | °C |
| blood_pressure | varchar(20) | e.g., "120/80" |
| cardiac_rate | int | bpm |
| respiratory_rate | int | /min |
| weight | decimal(5,2) | kg |
| height | decimal(5,2) | cm |
| head_circumference | decimal(5,2) | cm (pedia) |
| chest_circumference | decimal(5,2) | cm (pedia) |
| fetal_heart_tone | int | bpm (OB) |
| fundal_height | decimal(5,2) | cm (OB) |
| last_menstrual_period | date | (OB) |
| vital_signs_recorded_at | timestamp | |
| **Doctor Assessment** | | |
| pertinent_hpi_pe | text | History/Physical exam |
| diagnosis | text | |
| plan | text | Treatment plan |
| procedures_done | text | |
| prescription_notes | text | General Rx notes |
| examined_at | timestamp | |
| examination_ended_at | timestamp | |
| examination_time | enum | am, pm |
| **Billing Hints** | | |
| suggested_discount_type | enum | none, family, senior, pwd, employee, other |
| suggested_discount_reason | text | |
| **Status** | | |
| status | enum | in_progress, for_billing, for_admission, completed |
| timestamps | | |

**Record Number Generation:**
```php
MR-{YEAR}-{5-digit sequence}
// Example: MR-2026-00001
```

---

### 8. prescriptions

Medications prescribed during visit.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| medical_record_id | bigint | FK |
| hospital_drug_id | bigint | FK, nullable |
| medication_name | varchar(255) | Drug name |
| dosage | varchar(255) | e.g., "500mg" |
| frequency | varchar(255) | e.g., "3x daily" |
| duration | varchar(255) | e.g., "7 days" |
| quantity | int | |
| unit | varchar(50) | tablets, capsules, ml |
| instructions | text | Additional notes |
| is_hospital_drug | boolean | From hospital inventory |
| timestamps | | |

---

### 9. hospital_drugs

Drug inventory with pricing.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(255) | |
| generic_name | varchar(255) | |
| category | varchar(100) | |
| unit | varchar(50) | |
| unit_price | decimal(10,2) | |
| stock_quantity | int | |
| reorder_level | int | Alert threshold |
| is_active | boolean | |
| timestamps | | |

---

### 10. services

Billable services/procedures.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(255) | |
| category | varchar(100) | |
| base_price | decimal(10,2) | |
| is_active | boolean | |
| timestamps | | |

---

### 11. billing_transactions

Payment records per visit.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| transaction_number | varchar(20) | Unique |
| medical_record_id | bigint | FK |
| user_id | bigint | FK (account owner) |
| processed_by | bigint | FK → users (cashier) |
| **Amounts** | | |
| subtotal | decimal(12,2) | Before discount |
| discount_type | enum | none, senior, pwd, employee, family, other |
| discount_percentage | decimal(5,2) | |
| discount_amount | decimal(12,2) | |
| additional_charges | decimal(12,2) | Emergency fees, etc. |
| total_amount | decimal(12,2) | Final amount |
| amount_paid | decimal(12,2) | |
| change_amount | decimal(12,2) | |
| **Payment** | | |
| payment_method | enum | cash, card, gcash, maya, bank_transfer |
| payment_reference | varchar(100) | Reference number |
| **Status** | | |
| status | enum | pending, paid, partial, cancelled |
| paid_at | timestamp | |
| timestamps | | |

---

### 12. billing_items

Line items in a bill.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| billing_transaction_id | bigint | FK |
| item_type | enum | consultation, service, drug, other |
| item_id | bigint | FK to source table |
| item_description | varchar(255) | |
| quantity | int | |
| unit_price | decimal(10,2) | |
| total_price | decimal(10,2) | |
| timestamps | | |

---

### 13. admissions

Hospital admission records.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| medical_record_id | bigint | FK |
| user_id | bigint | FK |
| admission_number | varchar(20) | Unique |
| admitted_by | bigint | FK → users |
| room_number | varchar(20) | |
| bed_number | varchar(20) | |
| admitted_at | timestamp | |
| discharged_at | timestamp | |
| status | enum | admitted, discharged, transferred |
| notes | text | |
| timestamps | | |

---

### 14. system_settings

Application configuration (key-value store).

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| key | varchar(255) | Unique |
| value | text | JSON or string |
| timestamps | | |

**Example Settings:**
- `max_advance_booking_days`: 30
- `allow_same_day_booking`: true
- `clinic_open_time`: "08:00"
- `clinic_close_time`: "17:00"

---

## Supporting Tables

### queue_displays

Configuration for TV queue monitors.

| Column | Type |
|--------|------|
| id | bigint |
| consultation_type_id | bigint |
| display_name | varchar(255) |
| location | varchar(255) |
| is_active | boolean |

### user_devices

Mobile device registration for push notifications.

| Column | Type |
|--------|------|
| id | bigint |
| user_id | bigint |
| device_token | varchar(255) |
| device_type | enum |
| is_active | boolean |

### notification_logs

Notification history.

| Column | Type |
|--------|------|
| id | bigint |
| user_id | bigint |
| type | varchar(255) |
| data | json |
| sent_at | timestamp |

### sms_logs

SMS message history.

| Column | Type |
|--------|------|
| id | bigint |
| phone_number | varchar(20) |
| message | text |
| status | enum |
| sent_at | timestamp |

---

## Database Indexes

### Performance Indexes

```sql
-- Patient lookup in medical_records
CREATE INDEX patient_lookup_index ON medical_records
    (patient_first_name, patient_last_name, patient_date_of_birth);

-- Queue daily lookup
CREATE INDEX idx_queues_date_status ON queues (queue_date, status);

-- Appointment date lookup
CREATE INDEX idx_appointments_date ON appointments (appointment_date, status);
```

### Unique Constraints

```sql
-- Unique queue per session
UNIQUE (queue_number, queue_date, consultation_type_id, session_number)

-- Unique record number
UNIQUE (record_number)

-- Unique transaction number
UNIQUE (transaction_number)
```

---

## Database Configuration

### Development (SQLite)

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### Production (MySQL)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hqms
DB_USERNAME=hqms_user
DB_PASSWORD=secure_password
```

---

## Migration Commands

```bash
# Run all migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Fresh migration (drops all tables)
php artisan migrate:fresh

# With seeders
php artisan migrate:fresh --seed
```
