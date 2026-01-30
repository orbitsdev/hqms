<?php

namespace App\Models;

use App\Traits\Models\MedicalRecordRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory, MedicalRecordRelations;

    protected $guarded = ['id'];

    protected $casts = [
        'patient_date_of_birth' => 'date',
        'visit_date' => 'date',
        'time_in' => 'datetime',
        'last_menstrual_period' => 'date',
        'vital_signs_recorded_at' => 'datetime',
        'examined_at' => 'datetime',
        'examination_ended_at' => 'datetime',
        'doctor_fee_override' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (MedicalRecord $record): void {
            if ($record->record_number !== null) {
                return;
            }

            $record->record_number = static::generateRecordNumber();
        });
    }

    public static function generateRecordNumber(): string
    {
        $year = now()->year;

        $lastRecord = static::query()
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $sequence = $lastRecord === null
            ? 1
            : (int) substr((string) $lastRecord->record_number, -5) + 1;

        return sprintf('MR-%d-%05d', $year, $sequence);
    }

    public function getPatientFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->patient_first_name,
            $this->patient_middle_name,
            $this->patient_last_name,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Get patient's current age (calculated from DOB).
     */
    public function getPatientAgeAttribute(): ?int
    {
        if (! $this->patient_date_of_birth) {
            return null;
        }

        return $this->patient_date_of_birth->age;
    }

    /**
     * Get patient's age at time of visit (calculated from DOB + visit_date).
     * Returns formatted string like "2 years 6 months" or "3 months".
     */
    public function getPatientAgeAtVisitAttribute(): ?string
    {
        $calculated = $this->calculateAgeFromDob();
        if ($calculated === null) {
            return null;
        }

        $years = $calculated['years'];
        $months = $calculated['months'];

        if ($years === 0 && $months === 0) {
            return __('Newborn');
        }

        if ($years === 0) {
            return $months === 1
                ? __(':months month', ['months' => $months])
                : __(':months months', ['months' => $months]);
        }

        if ($months === 0) {
            return $years === 1
                ? __(':years year', ['years' => $years])
                : __(':years years', ['years' => $years]);
        }

        $yearText = $years === 1 ? __(':years year', ['years' => $years]) : __(':years years', ['years' => $years]);
        $monthText = $months === 1 ? __(':months month', ['months' => $months]) : __(':months months', ['months' => $months]);

        return "{$yearText} {$monthText}";
    }

    /**
     * Get patient's age at visit as short format (e.g., "2y 6m" or "3m").
     */
    public function getPatientAgeAtVisitShortAttribute(): ?string
    {
        $calculated = $this->calculateAgeFromDob();
        if ($calculated === null) {
            return null;
        }

        $years = $calculated['years'];
        $months = $calculated['months'];

        if ($years === 0 && $months === 0) {
            return __('NB'); // Newborn
        }

        if ($years === 0) {
            return "{$months}m";
        }

        if ($months === 0) {
            return "{$years}y";
        }

        return "{$years}y {$months}m";
    }

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
}
