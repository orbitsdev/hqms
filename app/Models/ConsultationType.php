<?php

namespace App\Models;

use App\Traits\Models\ConsultationTypeRelations;
use Illuminate\Database\Eloquent\Model;

class ConsultationType extends Model
{
    use ConsultationTypeRelations;

    protected $guarded = ['id'];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];
}
