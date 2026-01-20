<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserDevice>
 */
class UserDeviceFactory extends Factory
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
            'device_id' => fake()->uuid(),
            'device_model' => fake()->randomElement(['iPhone 15 Pro', 'Samsung Galaxy S24', 'Pixel 8', 'Xiaomi 14']),
            'platform' => fake()->randomElement(['android', 'ios']),
            'app_version' => fake()->semver(),
            'fcm_token' => fake()->sha256(),
            'is_active' => true,
            'last_used_at' => now(),
        ];
    }

    /**
     * Indicate that the device is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the device has no FCM token.
     */
    public function withoutFcmToken(): static
    {
        return $this->state(fn (array $attributes) => [
            'fcm_token' => null,
        ]);
    }

    /**
     * Indicate that the device is Android.
     */
    public function android(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'android',
            'device_model' => fake()->randomElement(['Samsung Galaxy S24', 'Pixel 8', 'Xiaomi 14', 'OnePlus 12']),
        ]);
    }

    /**
     * Indicate that the device is iOS.
     */
    public function ios(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'ios',
            'device_model' => fake()->randomElement(['iPhone 15 Pro', 'iPhone 15', 'iPhone 14 Pro', 'iPad Pro']),
        ]);
    }
}
