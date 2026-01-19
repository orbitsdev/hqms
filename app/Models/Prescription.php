<?php

namespace App\Models;

use App\Traits\Models\PrescriptionRelations;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use PrescriptionRelations;

    protected $guarded = ['id'];

    protected $casts = [
        'is_hospital_drug' => 'boolean',
    ];
}
