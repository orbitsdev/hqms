<?php

namespace App\Traits\Models;

use App\Models\HospitalDrug;
use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait PrescriptionRelations
{
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }

    public function hospitalDrug(): BelongsTo
    {
        return $this->belongsTo(HospitalDrug::class);
    }
}
