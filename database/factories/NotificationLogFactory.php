<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NotificationLog>
 */
class NotificationLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            'appointment_approved',
            'appointment_declined',
            'queue_called',
            'queue_nearby',
            'appointment_reminder',
        ];

        return [
            'user_id' => User::factory(),
            'notification_type' => fake()->randomElement($types),
            'title' => fake()->sentence(4),
            'body' => fake()->sentence(10),
            'data' => ['appointment_id' => fake()->numberBetween(1, 100)],
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }

    /**
     * Indicate that the notification is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'sent_at' => null,
        ]);
    }

    /**
     * Indicate that the notification failed to send.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => 'Failed to send notification',
        ]);
    }

    /**
     * Indicate that the notification is an appointment approval.
     */
    public function appointmentApproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'appointment_approved',
            'title' => 'Appointment Approved',
            'body' => 'Your appointment has been approved. Your queue number will be assigned soon.',
        ]);
    }

    /**
     * Indicate that the notification is a queue call.
     */
    public function queueCalled(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'queue_called',
            'title' => 'Your Turn!',
            'body' => 'Please proceed to the consultation room.',
        ]);
    }

    /**
     * Indicate that the notification is a queue nearby alert.
     */
    public function queueNearby(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'queue_nearby',
            'title' => 'Almost Your Turn',
            'body' => 'You are 2 patients away. Please prepare.',
        ]);
    }
}
