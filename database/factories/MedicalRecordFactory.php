<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicalRecord>
 */
class MedicalRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);
        $timeIn = now();

        return [
            // record_number is auto-generated via model's booted() event
            'user_id' => User::factory(),
            'consultation_type_id' => ConsultationType::factory(),
            'appointment_id' => null,
            'queue_id' => null,
            'doctor_id' => null,
            'nurse_id' => null,

            // Patient Information
            'patient_first_name' => fake()->firstName($gender),
            'patient_middle_name' => fake()->optional()->lastName(),
            'patient_last_name' => fake()->lastName(),
            'patient_date_of_birth' => fake()->dateTimeBetween('-80 years', '-1 year')->format('Y-m-d'),
            'patient_gender' => $gender,
            'patient_marital_status' => fake()->randomElement(['child', 'single', 'married', 'widow']),

            // Patient Address
            'patient_province' => 'Sultan Kudarat',
            'patient_municipality' => fake()->randomElement(['Tacurong City', 'Isulan', 'Esperanza', 'Lebak']),
            'patient_barangay' => fake()->randomElement(['Poblacion', 'New Isabela', 'San Emmanuel', 'Calean']),
            'patient_street' => fake()->streetAddress(),

            // Patient Contact
            'patient_contact_number' => '09'.fake()->numerify('#########'),
            'patient_occupation' => fake()->jobTitle(),

            // Medical Background
            'patient_blood_type' => fake()->optional()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'patient_allergies' => fake()->optional()->randomElement(['None', 'Penicillin', 'Sulfa drugs', 'Aspirin']),
            'patient_chronic_conditions' => fake()->optional()->randomElement(['None', 'Hypertension', 'Diabetes', 'Asthma']),

            // Emergency Contact
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => '09'.fake()->numerify('#########'),

            // Visit Information
            'visit_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'time_in' => $timeIn,
            'time_in_period' => $timeIn->format('a') === 'am' ? 'am' : 'pm',
            'visit_type' => fake()->randomElement(['new', 'old', 'revisit']),
            'service_type' => 'checkup',
            'service_category' => fake()->randomElement(['surgical', 'non-surgical']),

            // Chief Complaints
            'chief_complaints_initial' => fake()->sentence(),
            'chief_complaints_updated' => fake()->optional()->sentence(),

            // Vital Signs
            'temperature' => fake()->randomFloat(1, 36.0, 39.0),
            'blood_pressure' => fake()->numerify('1##/##'),
            'cardiac_rate' => fake()->numberBetween(60, 100),
            'respiratory_rate' => fake()->numberBetween(12, 20),
            'weight' => fake()->randomFloat(2, 3.0, 120.0),
            'height' => fake()->randomFloat(2, 50.0, 190.0),

            'vital_signs_recorded_at' => now(),

            // Doctor Recommendations
            'suggested_discount_type' => 'none',

            // Status
            'status' => 'in_progress',
        ];
    }

    /**
     * Indicate the record is for OB consultation.
     */
    public function ob(): static
    {
        return $this->state(fn (array $attributes) => [
            'patient_gender' => 'female',
            'patient_marital_status' => 'married',
            'ob_type' => fake()->randomElement(['prenatal', 'post-natal']),
            'fetal_heart_tone' => fake()->numberBetween(120, 160),
            'fundal_height' => fake()->randomFloat(2, 20.0, 40.0),
            'last_menstrual_period' => fake()->dateTimeBetween('-40 weeks', '-4 weeks')->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate the record is for PEDIA consultation.
     */
    public function pedia(): static
    {
        return $this->state(fn (array $attributes) => [
            'patient_date_of_birth' => fake()->dateTimeBetween('-12 years', '-1 month')->format('Y-m-d'),
            'patient_marital_status' => 'child',
            'head_circumference' => fake()->randomFloat(2, 30.0, 55.0),
            'chest_circumference' => fake()->randomFloat(2, 30.0, 80.0),
        ]);
    }

    /**
     * Indicate the record has been examined by doctor.
     */
    public function examined(): static
    {
        return $this->state(fn (array $attributes) => [
            'pertinent_hpi_pe' => fake()->paragraph(),
            'diagnosis' => fake()->sentence(),
            'plan' => fake()->sentence(),
            'procedures_done' => fake()->optional()->sentence(),
            'prescription_notes' => fake()->optional()->sentence(),
            'examined_at' => now(),
            'examination_ended_at' => now()->addMinutes(20),
            'examination_time' => fake()->randomElement(['am', 'pm']),
        ]);
    }

    /**
     * Indicate the record is completed.
     */
    public function completed(): static
    {
        return $this->examined()->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate the record is ready for billing.
     */
    public function forBilling(): static
    {
        return $this->examined()->state(fn (array $attributes) => [
            'status' => 'for_billing',
        ]);
    }

    /**
     * Associate with an appointment.
     */
    public function forAppointment(Appointment $appointment): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $appointment->user_id,
            'consultation_type_id' => $appointment->consultation_type_id,
            'appointment_id' => $appointment->id,
            'visit_date' => $appointment->appointment_date,
        ]);
    }
}
