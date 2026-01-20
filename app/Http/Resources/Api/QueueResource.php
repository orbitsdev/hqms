<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'queue_number' => $this->queue_number,
            'formatted_number' => $this->formatted_number,
            'queue_date' => $this->queue_date->format('Y-m-d'),
            'estimated_time' => $this->estimated_time?->format('H:i'),
            'priority' => $this->priority,
            'status' => $this->status,
            'source' => $this->source,

            // Position info
            'position' => $this->when(isset($this->position), $this->position),
            'ahead_count' => $this->when(isset($this->ahead_count), $this->ahead_count),

            // Consultation Type
            'consultation_type' => $this->when(
                $this->relationLoaded('consultationType'),
                fn () => [
                    'id' => $this->consultationType->id,
                    'name' => $this->consultationType->name,
                    'code' => $this->consultationType->code,
                ]
            ),

            // Doctor (if assigned)
            'doctor' => $this->when(
                $this->relationLoaded('doctor') && $this->doctor,
                fn () => [
                    'id' => $this->doctor->id,
                    'name' => $this->doctor->personalInformation?->full_name ?? 'Dr. '.$this->doctor->email,
                ]
            ),

            // Appointment info
            'appointment' => $this->when(
                $this->relationLoaded('appointment') && $this->appointment,
                fn () => [
                    'id' => $this->appointment->id,
                    'chief_complaints' => $this->appointment->chief_complaints,
                ]
            ),

            // Timestamps
            'called_at' => $this->called_at?->toIso8601String(),
            'serving_started_at' => $this->serving_started_at?->toIso8601String(),
            'serving_ended_at' => $this->serving_ended_at?->toIso8601String(),

            // Computed times
            'wait_time_minutes' => $this->wait_time,
            'service_time_minutes' => $this->service_time,

            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
