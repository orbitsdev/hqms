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
        $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;

        // Check for exception on this specific date
        $exception = $this->doctorSchedules()
            ->where('schedule_type', 'exception')
            ->where('date', $date)
            ->first();

        if ($exception) {
            return $exception->is_available;
        }

        // Check regular schedule for this day of week
        return $this->doctorSchedules()
            ->where('schedule_type', 'regular')
            ->where('day_of_week', $dayOfWeek)
            ->exists();
    }
}
