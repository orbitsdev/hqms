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

        // OB Doctor: Mon, Wed, Fri (full day - uses system hours)
        if ($obDoctor && $obType) {
            foreach ([1, 3, 5] as $dayOfWeek) { // Mon=1, Wed=3, Fri=5
                DoctorSchedule::create([
                    'user_id' => $obDoctor->id,
                    'consultation_type_id' => $obType->id,
                    'schedule_type' => 'regular',
                    'day_of_week' => $dayOfWeek,
                ]);
            }
        }

        // PEDIA Doctor: Tue, Thu, Sat (full day - uses system hours)
        if ($pedDoctor && $pedType) {
            foreach ([2, 4, 6] as $dayOfWeek) { // Tue=2, Thu=4, Sat=6
                DoctorSchedule::create([
                    'user_id' => $pedDoctor->id,
                    'consultation_type_id' => $pedType->id,
                    'schedule_type' => 'regular',
                    'day_of_week' => $dayOfWeek,
                ]);
            }
        }

        // General Doctor: Mon-Fri (full day - uses system hours)
        if ($genDoctor && $genType) {
            foreach ([1, 2, 3, 4, 5] as $dayOfWeek) {
                DoctorSchedule::create([
                    'user_id' => $genDoctor->id,
                    'consultation_type_id' => $genType->id,
                    'schedule_type' => 'regular',
                    'day_of_week' => $dayOfWeek,
                ]);
            }
        }
    }
}
