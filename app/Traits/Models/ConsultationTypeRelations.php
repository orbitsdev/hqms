<?php
namespace App\Traits\Models;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\Queue;
use App\Models\QueueDisplay;
use App\Models\User;

trait ConsultationTypeRelations
{
    public function doctors()
    {
        return $this->belongsToMany(User::class, 'doctor_consultation_types');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    public function doctorSchedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function queueDisplays()
    {
        return $this->hasMany(QueueDisplay::class);
    }

    public function getTodayQueueCount(): int
    {
        return $this->queues()
            ->where('queue_date', today())
            ->count();
    }

    public function isAcceptingAppointments($date): bool
    {
        $count = $this->appointments()
            ->where('appointment_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        return $count < $this->max_daily_patients;
    }
}
