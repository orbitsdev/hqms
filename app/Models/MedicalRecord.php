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

    public function getPatientAgeAttribute(): ?int
    {
        if (! $this->patient_date_of_birth) {
            return null;
        }

        return $this->patient_date_of_birth->age;
    }
}
