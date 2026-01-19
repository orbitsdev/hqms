<?php
namespace App\Traits\Models;

use App\Models\DoctorSchedule;
use App\Models\PersonalInformation;

trait UserRelations
{
    public function personalInformation() {
        return $this->hasOne(PersonalInformation::class);
    }

     public function doctorSchedules() {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id');
    }
}
