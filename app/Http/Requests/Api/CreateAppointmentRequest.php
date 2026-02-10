<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'consultation_type_id' => ['required', 'exists:consultation_types,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['nullable', 'date_format:H:i'],
            'chief_complaints' => ['nullable', 'string', 'max:2000'],
            'visit_type' => ['required', 'in:new,old,revisit'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'consultation_type_id.required' => 'Please select a consultation type.',
            'consultation_type_id.exists' => 'The selected consultation type is invalid.',
            'appointment_date.required' => 'Please select an appointment date.',
            'appointment_date.after_or_equal' => 'Appointment date must be today or a future date.',
            'appointment_time.date_format' => 'Invalid time format. Please use HH:MM format.',
            'chief_complaints.max' => 'Chief complaints must not exceed 2000 characters.',
            'visit_type.required' => 'Please select a visit type (New, Old, or Revisit).',
            'visit_type.in' => 'Visit type must be new, old, or revisit.',
        ];
    }
}
