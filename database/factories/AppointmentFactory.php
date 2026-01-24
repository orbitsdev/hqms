<?php

namespace Database\Factories;

use App\Models\ConsultationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'consultation_type_id' => ConsultationType::factory(),
            'patient_first_name' => fake()->firstName(),
            'patient_middle_name' => fake()->optional()->firstName(),
            'patient_last_name' => fake()->lastName(),
            'patient_date_of_birth' => fake()->dateTimeBetween('-80 years', '-1 year')->format('Y-m-d'),
            'patient_gender' => fake()->randomElement(['male', 'female']),
            'patient_phone' => fake()->optional()->phoneNumber(),
            'patient_province' => fake()->optional()->state(),
            'patient_municipality' => fake()->optional()->city(),
            'patient_barangay' => fake()->optional()->streetName(),
            'patient_street' => fake()->optional()->streetAddress(),
            'relationship_to_account' => fake()->randomElement(['self', 'child', 'spouse', 'parent', 'sibling']),
            'appointment_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'appointment_time' => fake()->optional()->time('H:i'),
            'chief_complaints' => fake()->optional()->paragraph(),
            'status' => 'pending',
            'source' => 'online',
        ];
    }

    /**
     * Indicate that the appointment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the appointment is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancellation_reason' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the appointment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'appointment_date' => fake()->dateTimeBetween('-30 days', '-1 day')->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the appointment is a walk-in.
     */
    public function walkIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'walk-in',
            'appointment_date' => today()->format('Y-m-d'),
            'status' => 'approved',
        ]);
    }
}
