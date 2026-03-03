<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Queue;
use Illuminate\Support\Facades\DB;

class QueueNumberService
{
    /**
     * Generate the next sequential queue number for a consultation type on a given date.
     */
    public function generateQueueNumber(int $consultationTypeId, string $date): int
    {
        $lastQueue = Queue::query()
            ->where('consultation_type_id', $consultationTypeId)
            ->where('queue_date', $date)
            ->where('session_number', 1)
            ->max('queue_number');

        return ($lastQueue ?? 0) + 1;
    }

    /**
     * Create a queue entry for an appointment and mark it as approved.
     *
     * @return Queue The created queue entry.
     */
    public function createQueueForAppointment(Appointment $appointment, ?int $approvedBy = null): Queue
    {
        return DB::transaction(function () use ($appointment, $approvedBy): Queue {
            $queueNumber = $this->generateQueueNumber(
                $appointment->consultation_type_id,
                $appointment->appointment_date->toDateString(),
            );

            $queue = Queue::create([
                'appointment_id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'consultation_type_id' => $appointment->consultation_type_id,
                'doctor_id' => $appointment->doctor_id,
                'queue_number' => $queueNumber,
                'queue_date' => $appointment->appointment_date,
                'session_number' => 1,
                'priority' => 'normal',
                'status' => 'waiting',
                'source' => $appointment->source,
            ]);

            $appointment->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            return $queue;
        });
    }

    /**
     * Predict the queue position for a future appointment.
     */
    public function predictQueuePosition(int $consultationTypeId, string $date): int
    {
        $existingCount = Appointment::query()
            ->where('consultation_type_id', $consultationTypeId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['confirmed', 'approved'])
            ->count();

        return $existingCount + 1;
    }
}
