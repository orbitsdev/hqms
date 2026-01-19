<?php

namespace App\Models;

use App\Traits\Models\DoctorScheduleRelations;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    use DoctorScheduleRelations;
}
