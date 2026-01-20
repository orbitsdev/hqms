<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // Patient Information
            'patient' => [
                'first_name' => $this->patient_first_name,
                'middle_name' => $this->patient_middle_name,
                'last_name' => $this->patient_last_name,
                'full_name' => $this->patient_full_name,
                'date_of_birth' => $this->patient_date_of_birth?->format('Y-m-d'),
                'age' => $this->patient_age,
                'gender' => $this->patient_gender,
                'marital_status' => $this->patient_marital_status,
                'blood_type' => $this->patient_blood_type,
                'allergies' => $this->patient_allergies,
                'chronic_conditions' => $this->patient_chronic_conditions,
                'contact_number' => $this->patient_contact_number,
                'occupation' => $this->patient_occupation,
                'address' => [
                    'street' => $this->patient_street,
                    'barangay' => $this->patient_barangay,
                    'municipality' => $this->patient_municipality,
                    'province' => $this->patient_province,
                    'full_address' => $this->patient_full_address,
                ],
                'emergency_contact' => [
                    'name' => $this->emergency_contact_name,
                    'phone' => $this->emergency_contact_phone,
                ],
            ],

            // Visit Information
            'visit' => [
                'date' => $this->visit_date?->format('Y-m-d'),
                'type' => $this->visit_type,
                'service_type' => $this->service_type,
            ],

            // Chief Complaints
            'chief_complaints' => [
                'initial' => $this->chief_complaints_initial,
                'updated' => $this->chief_complaints_updated,
                'effective' => $this->effective_chief_complaints,
            ],

            // Vital Signs
            'vital_signs' => [
                'temperature' => $this->temperature,
                'blood_pressure' => $this->blood_pressure,
                'cardiac_rate' => $this->cardiac_rate,
                'respiratory_rate' => $this->respiratory_rate,
                'weight' => $this->weight,
                'height' => $this->height,
                'head_circumference' => $this->head_circumference,
                'chest_circumference' => $this->chest_circumference,
                'fetal_heart_tone' => $this->fetal_heart_tone,
                'fundal_height' => $this->fundal_height,
                'last_menstrual_period' => $this->last_menstrual_period?->format('Y-m-d'),
                'recorded_at' => $this->vital_signs_recorded_at?->toIso8601String(),
            ],

            // Diagnosis (Doctor Input)
            'examination' => [
                'pertinent_hpi_pe' => $this->pertinent_hpi_pe,
                'diagnosis' => $this->diagnosis,
                'plan' => $this->plan,
                'procedures_done' => $this->procedures_done,
                'prescription_notes' => $this->prescription_notes,
                'examined_at' => $this->examined_at?->toIso8601String(),
                'examination_time' => $this->examination_time,
            ],

            // Status
            'status' => $this->status,

            // Relations
            'consultation_type' => $this->whenLoaded('consultationType', function () {
                return [
                    'id' => $this->consultationType->id,
                    'name' => $this->consultationType->name,
                    'code' => $this->consultationType->code,
                ];
            }),

            'doctor' => $this->whenLoaded('doctor', function () {
                return [
                    'id' => $this->doctor->id,
                    'name' => $this->doctor->personalInformation?->full_name,
                ];
            }),

            'nurse' => $this->whenLoaded('nurse', function () {
                return [
                    'id' => $this->nurse->id,
                    'name' => $this->nurse->personalInformation?->full_name,
                ];
            }),

            'prescriptions' => PrescriptionResource::collection($this->whenLoaded('prescriptions')),

            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
