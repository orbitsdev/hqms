<?php

namespace App\Models;

use App\Traits\Models\AdmissionRelations;
use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    use AdmissionRelations;

    protected $guarded = ['id'];

    protected $casts = [
        'admission_date' => 'datetime',
        'discharge_date' => 'datetime',
    ];
}
