<?php
namespace App\Traits\Models;

use App\Models\MedicalRecord;
use App\Models\User;

trait AdmissionRelations
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function admittedBy()
    {
        return $this->belongsTo(User::class, 'admitted_by');
    }

    public function getLengthOfStayAttribute(): int
    {
        if (!$this->discharge_date) {
            return $this->admission_date->diffInDays(now());
        }

        return $this->admission_date->diffInDays($this->discharge_date);
    }
}
