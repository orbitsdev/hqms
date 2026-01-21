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
        ConsultationType::create([
            'code' => 'ob',
            'name' => 'Obstetrics',
            'short_name' => 'O',
            'description' => 'Pregnancy and maternal care',
            'avg_duration' => 30,
        ]);

        ConsultationType::create([
            'code' => 'pedia',
            'name' => 'Pediatrics',
            'short_name' => 'P',
            'description' => 'Children healthcare',
            'avg_duration' => 25,
        ]);

        ConsultationType::create([
            'code' => 'general',
            'name' => 'General Medicine',
            'short_name' => 'G',
            'description' => 'General medical consultation',
            'avg_duration' => 20,
        ]);
    }
}
