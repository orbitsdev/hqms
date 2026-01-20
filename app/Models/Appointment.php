<?php

namespace App\Models;

use App\Traits\Models\AppointmentRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use AppointmentRelations, HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'approved_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'suggested_date' => 'date',
        'patient_date_of_birth' => 'date',
    ];
}
