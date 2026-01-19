<?php

namespace App\Models;

use App\Traits\Models\HospitalDrugRelations;
use Illuminate\Database\Eloquent\Model;

class HospitalDrug extends Model
{
    use HospitalDrugRelations;

    protected $guarded = ['id'];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
