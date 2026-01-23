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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait UserRelations
{
    public function personalInformation(): HasOne
    {
        return $this->hasOne(PersonalInformation::class);
    }

    public function consultationTypes(): BelongsToMany
    {
        return $this->belongsToMany(ConsultationType::class, 'doctor_consultation_types');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    public function doctorAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function doctorSchedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class, 'user_id');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'user_id');
    }

    public function billingTransactions(): HasMany
    {
        return $this->hasMany(BillingTransaction::class, 'user_id');
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class, 'user_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function activeDevices(): HasMany
    {
        return $this->devices()->active()->withFcmToken();
    }
}
