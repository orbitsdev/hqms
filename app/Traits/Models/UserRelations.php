<?php

namespace App\Traits\Models;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\BillingTransaction;
use App\Models\ConsultationType;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Models\PersonalInformation;
use App\Models\Queue;
use App\Models\UserDevice;

trait UserRelations
{
    public function personalInformation()
    {
        return $this->hasOne(PersonalInformation::class);
    }

    public function consultationTypes()
    {
        return $this->belongsToMany(ConsultationType::class, 'doctor_consultation_types');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    public function doctorAppointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function doctorSchedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'user_id');
    }

    public function queues()
    {
        return $this->hasMany(Queue::class, 'user_id');
    }

    public function billingTransactions()
    {
        return $this->hasMany(BillingTransaction::class, 'user_id');
    }

    public function admissions()
    {
        return $this->hasMany(Admission::class, 'user_id');
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function activeDevices()
    {
        return $this->devices()->active()->withFcmToken();
    }
}
