<?php

namespace App\Traits\Models;

use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

trait AdmissionRelations
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function admittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admitted_by');
    }

    public function getLengthOfStayAttribute(): int
    {
        if (!$this->discharge_date instanceof Carbon) {
            return $this->admission_date->diffInDays(now());
        }

        return $this->admission_date->diffInDays($this->discharge_date);
    }
}
