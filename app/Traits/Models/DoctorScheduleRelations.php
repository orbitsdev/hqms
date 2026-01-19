<?php
namespace App\Traits\Models;

use App\Models\User;
use App\Models\ConsultationType;



trait DoctorScheduleRelations
{
    public function doctor() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function consultationType() {
        return $this->belongsTo(ConsultationType::class);
    }
}
