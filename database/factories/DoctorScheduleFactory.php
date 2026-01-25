<?php

namespace Database\Factories;

use App\Models\ConsultationType;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DoctorSchedule>
 */
class DoctorScheduleFactory extends Factory
{
    protected $model = DoctorSchedule::class;

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
            'schedule_type' => 'regular',
            'day_of_week' => fake()->numberBetween(0, 6),
            'date' => null,
            'is_available' => true,
            'start_time' => '08:00',
            'end_time' => '17:00',
            'reason' => null,
        ];
    }

    /**
     * Create a regular weekly schedule.
     */
    public function regular(int $dayOfWeek = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => 'regular',
            'day_of_week' => $dayOfWeek,
            'date' => null,
        ]);
    }

    /**
     * Create an exception schedule (e.g., leave or extra day).
     */
    public function exception(?string $date = null): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => 'exception',
            'day_of_week' => null,
            'date' => $date ?? now()->addDays(7)->format('Y-m-d'),
        ]);
    }

    /**
     * Mark as unavailable (leave, holiday).
     */
    public function unavailable(string $reason = 'Leave'): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
            'start_time' => null,
            'end_time' => null,
            'reason' => $reason,
        ]);
    }

    /**
     * Mark as available (extra clinic day).
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => true,
        ]);
    }
}
