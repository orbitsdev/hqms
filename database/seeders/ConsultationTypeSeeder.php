<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConsultationType;


class ConsultationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ConsultationType::create([
            'code' => 'ob',
            'name' => 'Obstetrics',
            'short_name' => 'O',
            'description' => 'Pregnancy and maternal care',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'avg_duration' => 30,
            'max_daily_patients' => 40,
            'color_code' => '#FF6B9D',
            'display_order' => 1,
        ]);

        ConsultationType::create([
            'code' => 'pedia',
            'name' => 'Pediatrics',
            'short_name' => 'P',
            'description' => 'Children healthcare',
            'start_time' => '08:00',
            'end_time' => '15:00',
            'avg_duration' => 25,
            'max_daily_patients' => 35,
            'color_code' => '#4ECDC4',
            'display_order' => 2,
        ]);

        ConsultationType::create([
            'code' => 'general',
            'name' => 'General Medicine',
            'short_name' => 'G',
            'description' => 'General medical consultation',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'avg_duration' => 20,
            'max_daily_patients' => 50,
            'color_code' => '#95E1D3',
            'display_order' => 3,
        ]);
    }
}
