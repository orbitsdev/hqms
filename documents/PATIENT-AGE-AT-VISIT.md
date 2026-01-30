# Patient Age at Visit - Technical Documentation

## Overview

In medical records, it's important to record the patient's **age at the time of visit**, not their current age. This is especially critical for pediatric patients where age in months matters for treatment decisions.

## The Problem

If we only store Date of Birth (DOB) and calculate age using today's date:
- Viewing a record 2 years later would show the wrong age
- Medical history becomes inaccurate
- Treatment decisions based on age would be misleading

## The Solution

Calculate age using **DOB → visit_date** (not DOB → today).

```
Age at Visit = patient_date_of_birth → visit_date
```

### Example

| Patient DOB | Visit Date | Age at Visit | Current Age (2027) |
|-------------|------------|--------------|-------------------|
| 2010-07-11 | 2026-01-30 | **15y 6m** | 17y 5m |

The medical record will always show **15y 6m** regardless of when you view it.

---

## Implementation

### 1. Where `visit_date` is Set

**File:** `app/Livewire/Nurse/TodayQueue.php`
**Method:** `startServing()` (line ~505-546)

```php
public function startServing(int $queueId): void
{
    DB::transaction(function () use ($queue, $nurse): void {
        // ...

        if (! $queue->medicalRecord) {
            MedicalRecord::create([
                'visit_date' => today(),  // ← Set when nurse starts serving
                'time_in' => now(),
                // ...
            ]);
        }
    });
}
```

**Trigger:** Nurse clicks "Start Serving" button in Today's Queue.

### 2. Where Age is Calculated

**File:** `app/Models/MedicalRecord.php`

#### Main Calculator Method

```php
/**
 * Calculate age from DOB relative to visit date.
 *
 * @return array{years: int, months: int}|null
 */
public function calculateAgeFromDob(): ?array
{
    if (! $this->patient_date_of_birth || ! $this->visit_date) {
        return null;
    }

    $dob = $this->patient_date_of_birth;
    $visitDate = $this->visit_date;

    $diff = $dob->diff($visitDate);

    return [
        'years' => $diff->y,
        'months' => $diff->m,
    ];
}
```

#### Accessor - Full Format

```php
/**
 * Get patient's age at time of visit.
 * Returns: "2 years 6 months" or "3 months" or "Newborn"
 */
public function getPatientAgeAtVisitAttribute(): ?string
```

**Usage:** `$record->patient_age_at_visit`
**Output:** `"15 years 6 months"`

#### Accessor - Short Format

```php
/**
 * Get patient's age at visit as short format.
 * Returns: "2y 6m" or "3m" or "NB"
 */
public function getPatientAgeAtVisitShortAttribute(): ?string
```

**Usage:** `$record->patient_age_at_visit_short`
**Output:** `"15y 6m"`

---

## Where Age is Displayed

### Patient Portal
**File:** `resources/views/livewire/patient/medical-record-show.blade.php`
```blade
{{ $record->patient_age_at_visit ?? $record->patient_age . ' years old' }}
```

### Doctor Queue
**File:** `resources/views/livewire/doctor/patient-queue.blade.php`
```blade
{{ $record->patient_age_at_visit_short }}
```

### PDF Medical Report
**File:** `resources/views/pdf/medical-record.blade.php`
```blade
<td>Age:</td>
<td>{{ $record->patient_age_at_visit ?? ($record->patient_age ? $record->patient_age . ' years' : '-') }}</td>
```

### Nurse Queue (Selected Patient Panel)
**File:** `resources/views/livewire/nurse/today-queue.blade.php`
- Shows age from appointment data during queue
- After interview, uses calculated age from medical record

---

## Data Flow

```
┌─────────────────────────────────────────────────────────────┐
│  1. NURSE STARTS SERVING                                    │
│     └─→ MedicalRecord created with visit_date = today()     │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  2. NURSE INTERVIEWS PATIENT                                │
│     └─→ patient_date_of_birth saved (required field)        │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  3. AGE CALCULATION (on-the-fly)                            │
│     └─→ patient_date_of_birth.diff(visit_date)              │
│     └─→ Returns: { years: 15, months: 6 }                   │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  4. DISPLAY IN VIEWS/REPORTS                                │
│     └─→ $record->patient_age_at_visit = "15 years 6 months" │
│     └─→ $record->patient_age_at_visit_short = "15y 6m"      │
└─────────────────────────────────────────────────────────────┘
```

---

## Database Fields

| Field | Table | Description |
|-------|-------|-------------|
| `patient_date_of_birth` | medical_records | Patient's date of birth |
| `visit_date` | medical_records | Date when patient visited (set by nurse) |

**Note:** No separate `patient_age_years` or `patient_age_months` columns needed - age is calculated on-the-fly.

---

## Why This Approach?

| Benefit | Description |
|---------|-------------|
| **Accuracy** | Age is always correct relative to visit date |
| **No Redundancy** | No duplicate data storage |
| **Single Source of Truth** | DOB is the only source, age is derived |
| **Simpler Input** | Nurse only enters DOB, not age |
| **Medical Standard** | Matches how hospitals record patient age |

---

## Edge Cases

| Scenario | Handling |
|----------|----------|
| DOB not provided | Returns `null` (shouldn't happen - DOB is required) |
| visit_date not set | Returns `null` |
| Newborn (0y 0m) | Returns "Newborn" or "NB" |
| Infant (0 years) | Returns months only, e.g., "6 months" |

---

## Related Files

- `app/Models/MedicalRecord.php` - Model with age accessors
- `app/Livewire/Nurse/TodayQueue.php` - Sets visit_date, collects DOB
- `resources/views/pdf/medical-record.blade.php` - PDF report
- `resources/views/livewire/patient/medical-record-show.blade.php` - Patient view
- `resources/views/livewire/doctor/patient-queue.blade.php` - Doctor queue
