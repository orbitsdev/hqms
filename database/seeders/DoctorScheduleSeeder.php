<?php

namespace Database\Seeders;

use App\Models\ConsultationType;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get doctors by consultation type
        $obDoctor = User::whereHas('roles', fn ($q) => $q->where('name', 'doctor'))
            ->whereHas('consultationTypes', fn ($q) => $q->where('code', 'ob'))
            ->first();

        $pedDoctor = User::whereHas('roles', fn ($q) => $q->where('name', 'doctor'))
            ->whereHas('consultationTypes', fn ($q) => $q->where('code', 'pedia'))
            ->first();

        $genDoctor = User::whereHas('roles', fn ($q) => $q->where('name', 'doctor'))
            ->whereHas('consultationTypes', fn ($q) => $q->where('code', 'general'))
            ->first();

        $obType = ConsultationType::where('code', 'ob')->first();
        $pedType = ConsultationType::where('code', 'pedia')->first();
        $genType = ConsultationType::where('code', 'general')->first();

        // OB Doctor: Mon-Fri 8am-5pm
        if ($obDoctor && $obType) {
            foreach ([1, 2, 3, 4, 5] as $dayOfWeek) { // Mon=1, Fri=5
                DoctorSchedule::create([
                    'user_id' => $obDoctor->id,
                    'consultation_type_id' => $obType->id,
                    'schedule_type' => 'regular',
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '08:00',
                    'end_time' => '17:00',
                    'max_patients' => 30,
                    'is_available' => true,
                ]);
            }
        }

        // PEDIA Doctor: Mon-Sat 8am-3pm
        if ($pedDoctor && $pedType) {
            foreach ([1, 2, 3, 4, 5, 6] as $dayOfWeek) { // Mon=1, Sat=6
                DoctorSchedule::create([
                    'user_id' => $pedDoctor->id,
                    'consultation_type_id' => $pedType->id,
                    'schedule_type' => 'regular',
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '08:00',
                    'end_time' => '15:00',
                    'max_patients' => 25,
                    'is_available' => true,
                ]);
            }
        }

        // General Doctor: Mon-Fri 9am-6pm
        if ($genDoctor && $genType) {
            foreach ([1, 2, 3, 4, 5] as $dayOfWeek) {
                DoctorSchedule::create([
                    'user_id' => $genDoctor->id,
                    'consultation_type_id' => $genType->id,
                    'schedule_type' => 'regular',
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '09:00',
                    'end_time' => '18:00',
                    'max_patients' => 40,
                    'is_available' => true,
                ]);
            }
        }
    }
}
