<?php

namespace Database\Seeders;

use App\Models\ConsultationType;
use Illuminate\Database\Seeder;

class ConsultationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ConsultationType::updateOrCreate(
            ['code' => 'ob'],
            [
                'name' => 'Obstetrics',
                'short_name' => 'O',
                'description' => 'Pregnancy and maternal care',
                'avg_duration' => 30,
            ]
        );

        ConsultationType::updateOrCreate(
            ['code' => 'pedia'],
            [
                'name' => 'Pediatrics',
                'short_name' => 'P',
                'description' => 'Children healthcare',
                'avg_duration' => 25,
            ]
        );

        ConsultationType::updateOrCreate(
            ['code' => 'general'],
            [
                'name' => 'General Medicine',
                'short_name' => 'G',
                'description' => 'General medical consultation',
                'avg_duration' => 20,
            ]
        );
    }
}
