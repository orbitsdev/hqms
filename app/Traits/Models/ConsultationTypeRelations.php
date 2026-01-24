<?php

namespace App\Traits\Models;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\Queue;
use App\Models\QueueDisplay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait ConsultationTypeRelations
{
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'doctor_consultation_types');
    }


    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function doctorSchedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function queueDisplays(): HasMany
    {
        return $this->hasMany(QueueDisplay::class);
    }

    public function getTodayQueueCount(): int
    {
        return $this->queues()
            ->where('queue_date', today())
            ->count();
    }


    public function isAcceptingAppointments(string $dateString): bool
    {
        $date = Carbon::parse($dateString);
        $dayOfWeek = (int) $date->dayOfWeek; // 0 Sun .. 6 Sat

        // Get all schedules for this type
        $schedules = $this->doctorSchedules()->get();

        if ($schedules->isEmpty()) {
            // fallback rule if no schedules configured:
            return ! $date->isSunday();
        }

        // Doctors who are explicitly AVAILABLE for this date (extra clinic day)
        $extraAvailableDoctorIds = $schedules
            ->where('schedule_type', 'exception')
            ->where('is_available', true)
            ->where('date', $date->toDateString())
            ->pluck('user_id')
            ->unique();

        // Doctors who are explicitly NOT available for this date (leave)
        $blockedDoctorIds = $schedules
            ->where('schedule_type', 'exception')
            ->where('is_available', false)
            ->where('date', $date->toDateString())
            ->pluck('user_id')
            ->unique();

        // Doctors who have regular schedule on that day of week
        $regularDoctorIds = $schedules
            ->where('schedule_type', 'regular')
            ->where('day_of_week', $dayOfWeek)
            ->pluck('user_id')
            ->unique();

        // available doctors = (regular + extra) - blocked
        $availableDoctorIds = $regularDoctorIds
            ->merge($extraAvailableDoctorIds)
            ->unique()
            ->diff($blockedDoctorIds)
            ->values();

        return $availableDoctorIds->isNotEmpty();
    }
}
