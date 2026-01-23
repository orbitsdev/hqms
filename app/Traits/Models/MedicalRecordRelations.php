<?php

namespace App\Traits\Models;

use App\Models\Appointment;
use App\Models\BillingTransaction;
use App\Models\ConsultationType;
use App\Models\Prescription;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

trait MedicalRecordRelations
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function consultationType(): BelongsTo
    {
        return $this->belongsTo(ConsultationType::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function billingTransaction(): HasOne
    {
        return $this->hasOne(BillingTransaction::class);
    }

    public function getPatientFullNameAttribute(): string
    {
        $name = $this->patient_first_name;
        if ($this->patient_middle_name) {
            $name .= ' ' . $this->patient_middle_name;
        }
        $name .= ' ' . $this->patient_last_name;

        return $name;
    }

    public function getPatientFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->patient_street,
            $this->patient_barangay,
            $this->patient_municipality,
            $this->patient_province,
        ]);

        return implode(', ', $parts);
    }

    public function getPatientAgeAttribute(): ?int
    {
        if (!$this->patient_date_of_birth instanceof Carbon) {
            return null;
        }

        return $this->patient_date_of_birth->age;
    }

    public function getEffectiveChiefComplaintsAttribute(): ?string
    {
        return $this->chief_complaints_updated ?? $this->chief_complaints_initial;
    }

    public function scopeForPatient(Builder $query, string $firstName, string $lastName, ?string $dob = null): Builder
    {
        return $query->where('patient_first_name', $firstName)
            ->where('patient_last_name', $lastName)
            ->when($dob, fn (Builder $q) => $q->where('patient_date_of_birth', $dob));
    }
}
