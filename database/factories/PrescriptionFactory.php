<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prescription>
 */
class PrescriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $medications = [
            ['name' => 'Amoxicillin', 'dosage' => '500mg', 'frequency' => '3x daily', 'duration' => '7 days'],
            ['name' => 'Paracetamol', 'dosage' => '500mg', 'frequency' => 'every 4 hours as needed', 'duration' => '3 days'],
            ['name' => 'Cefalexin', 'dosage' => '500mg', 'frequency' => '4x daily', 'duration' => '7 days'],
            ['name' => 'Ibuprofen', 'dosage' => '400mg', 'frequency' => '3x daily after meals', 'duration' => '5 days'],
            ['name' => 'Omeprazole', 'dosage' => '20mg', 'frequency' => 'once daily before breakfast', 'duration' => '14 days'],
            ['name' => 'Metformin', 'dosage' => '500mg', 'frequency' => '2x daily with meals', 'duration' => '30 days'],
            ['name' => 'Losartan', 'dosage' => '50mg', 'frequency' => 'once daily', 'duration' => '30 days'],
            ['name' => 'Salbutamol Nebule', 'dosage' => '2.5mg', 'frequency' => 'every 6 hours', 'duration' => '5 days'],
            ['name' => 'Ferrous Sulfate', 'dosage' => '325mg', 'frequency' => 'once daily', 'duration' => '30 days'],
            ['name' => 'Folic Acid', 'dosage' => '5mg', 'frequency' => 'once daily', 'duration' => '30 days'],
        ];

        $medication = fake()->randomElement($medications);

        return [
            'medical_record_id' => MedicalRecord::factory(),
            'prescribed_by' => User::factory(),
            'medication_name' => $medication['name'],
            'dosage' => $medication['dosage'],
            'frequency' => $medication['frequency'],
            'duration' => $medication['duration'],
            'instructions' => fake()->optional()->randomElement([
                'Take after meals',
                'Take before bedtime',
                'Take with plenty of water',
                'Avoid dairy products',
                'Complete the full course',
            ]),
            'quantity' => fake()->numberBetween(10, 30),
            'hospital_drug_id' => null,
            'is_hospital_drug' => false,
        ];
    }

    /**
     * Indicate this is a hospital drug.
     */
    public function hospitalDrug(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hospital_drug' => true,
        ]);
    }
}
