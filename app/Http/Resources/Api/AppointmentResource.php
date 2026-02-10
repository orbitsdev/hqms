<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_date' => $this->appointment_date->format('Y-m-d'),
            'appointment_time' => $this->appointment_time?->format('H:i'),
            'chief_complaints' => $this->chief_complaints,
            'status' => $this->status,
            'source' => $this->source,
            'visit_type' => $this->visit_type,
            'visit_type_label' => match ($this->visit_type) {
                'new' => 'New Patient',
                'old' => 'Old Patient',
                'revisit' => 'Revisit',
                default => null,
            },

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

            // Queue info (if generated)
            'queue' => $this->when(
                $this->relationLoaded('queue') && $this->queue,
                fn () => [
                    'id' => $this->queue->id,
                    'number' => $this->queue->queue_number,
                    'formatted_number' => $this->queue->formatted_number,
                    'status' => $this->queue->status,
                    'estimated_time' => $this->queue->estimated_time?->format('H:i'),
                ]
            ),

            // Approval info
            'approved_by' => $this->when(
                $this->relationLoaded('approvedBy') && $this->approvedBy,
                fn () => [
                    'id' => $this->approvedBy->id,
                    'name' => $this->approvedBy->personalInformation?->full_name ?? $this->approvedBy->email,
                ]
            ),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),

            // Decline info
            'decline_reason' => $this->when($this->status === 'cancelled' || $this->decline_reason, $this->decline_reason),
            'suggested_date' => $this->when($this->suggested_date, $this->suggested_date?->format('Y-m-d')),
            'cancellation_reason' => $this->when($this->status === 'cancelled', $this->cancellation_reason),

            // Medical record (if completed)
            'has_medical_record' => $this->when(
                $this->relationLoaded('medicalRecord'),
                fn () => $this->medicalRecord !== null
            ),

            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
