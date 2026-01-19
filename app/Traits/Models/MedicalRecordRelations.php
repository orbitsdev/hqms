<?php
namespace App\Traits\Models;

use App\Models\Appointment;
use App\Models\BillingTransaction;
use App\Models\ConsultationType;
use App\Models\Prescription;
use App\Models\Queue;
use App\Models\User;

trait MedicalRecordRelations
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consultationType()
    {
        return $this->belongsTo(ConsultationType::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function billingTransaction()
    {
        return $this->hasOne(BillingTransaction::class);
    }

    // Patient Accessors
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
        if (!$this->patient_date_of_birth) return null;
        return $this->patient_date_of_birth->age;
    }

    public function getEffectiveChiefComplaintsAttribute(): ?string
    {
        return $this->chief_complaints_updated ?? $this->chief_complaints_initial;
    }

    // Scopes
    public function scopeForPatient($query, $firstName, $lastName, $dob = null)
    {
        return $query->where('patient_first_name', $firstName)
                     ->where('patient_last_name', $lastName)
                     ->when($dob, fn($q) => $q->where('patient_date_of_birth', $dob));
    }
}
