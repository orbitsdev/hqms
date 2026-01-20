<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Queue>
 */
class QueueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $queueNumber = 0;
        $queueNumber++;

        return [
            'appointment_id' => Appointment::factory(),
            'user_id' => User::factory(),
            'consultation_type_id' => ConsultationType::factory(),
            'queue_number' => $queueNumber,
            'queue_date' => today()->format('Y-m-d'),
            'estimated_time' => fake()->time('H:i'),
            'priority' => 'normal',
            'status' => 'waiting',
            'source' => 'online',
        ];
    }

    /**
     * Indicate that the queue is for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'queue_date' => today()->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the queue status is waiting.
     */
    public function waiting(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'waiting',
        ]);
    }

    /**
     * Indicate that the queue has been called.
     */
    public function called(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'called',
            'called_at' => now(),
        ]);
    }

    /**
     * Indicate that the queue is being served.
     */
    public function serving(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'serving',
            'called_at' => now()->subMinutes(5),
            'serving_started_at' => now(),
        ]);
    }

    /**
     * Indicate that the queue is completed (uses past date to avoid unique constraint issues).
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'queue_date' => today()->subDays(fake()->numberBetween(1, 30))->format('Y-m-d'),
            'called_at' => now()->subMinutes(30),
            'serving_started_at' => now()->subMinutes(25),
            'serving_ended_at' => now(),
        ]);
    }

    /**
     * Indicate that the queue was skipped.
     */
    public function skipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'skipped',
            'called_at' => now()->subMinutes(10),
        ]);
    }

    /**
     * Indicate that the queue is urgent priority.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * Indicate that the queue is from a walk-in.
     */
    public function walkIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => 'walk-in',
        ]);
    }
}
