<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConsultationType>
 */
class ConsultationTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            ['code' => 'test_ob', 'name' => 'Test Obstetrics', 'short_name' => 'TO'],
            ['code' => 'test_ped', 'name' => 'Test Pediatrics', 'short_name' => 'TP'],
            ['code' => 'test_gen', 'name' => 'Test General', 'short_name' => 'TG'],
        ];

        $type = fake()->randomElement($types);

        return [
            'code' => $type['code'].'_'.fake()->unique()->randomNumber(4),
            'name' => $type['name'],
            'short_name' => $type['short_name'],
            'description' => fake()->sentence(),
            'avg_duration' => 30,
            'max_daily_patients' => 50,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the consultation type is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
