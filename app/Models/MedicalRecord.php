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
        'last_menstrual_period' => 'date',
        'vital_signs_recorded_at' => 'datetime',
        'examined_at' => 'datetime',
        'is_pre_visit' => 'boolean',
    ];
}
