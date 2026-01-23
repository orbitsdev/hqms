<?php

namespace Database\Seeders;

use App\Models\ConsultationType;
use App\Models\QueueDisplay;
use Illuminate\Database\Seeder;

class QueueDisplaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $obType = ConsultationType::where('code', 'ob')->first();
        $pedType = ConsultationType::where('code', 'pedia')->first();
        $genType = ConsultationType::where('code', 'general')->first();

        if (!$obType || !$pedType || !$genType) {
            $this->command->warn('Consultation types missing; run ConsultationTypeSeeder before QueueDisplaySeeder.');

            return;
        }

        // OB Display
        QueueDisplay::create([
            'name' => 'OB Queue Display',
            'consultation_type_id' => $obType->id,
            'location' => 'OB Waiting Area',
            'display_settings' => [
                'font_size' => 'large',
                'theme' => 'light',
                'show_estimated_time' => true,
                'show_patient_count' => true,
                'sound_enabled' => true,
                'volume' => 80,
            ],
            'is_active' => true,
        ]);

        // PEDIA Display
        QueueDisplay::create([
            'name' => 'Pediatrics Queue Display',
            'consultation_type_id' => $pedType->id,
            'location' => 'Pediatrics Waiting Area',
            'display_settings' => [
                'font_size' => 'extra-large',
                'theme' => 'light',
                'show_estimated_time' => true,
                'show_patient_count' => true,
                'sound_enabled' => true,
                'volume' => 70,
            ],
            'is_active' => true,
        ]);

        // General Display
        QueueDisplay::create([
            'name' => 'General Medicine Queue Display',
            'consultation_type_id' => $genType->id,
            'location' => 'Main Waiting Area',
            'display_settings' => [
                'font_size' => 'large',
                'theme' => 'dark',
                'show_estimated_time' => true,
                'show_patient_count' => true,
                'sound_enabled' => true,
                'volume' => 75,
            ],
            'is_active' => true,
        ]);
    }
}
