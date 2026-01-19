<?php
namespace App\Traits\Models;

use App\Models\HospitalDrug;
use App\Models\MedicalRecord;
use App\Models\User;

trait PrescriptionRelations
{
    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }

    public function hospitalDrug()
    {
        return $this->belongsTo(HospitalDrug::class);
    }
}
