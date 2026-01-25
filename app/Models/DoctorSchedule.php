<?php

namespace App\Models;

use App\Traits\Models\DoctorScheduleRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    use DoctorScheduleRelations, HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_available' => 'boolean',
    ];
}
