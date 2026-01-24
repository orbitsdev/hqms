<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DoctorSchedule;
use Illuminate\Database\Seeder;
use App\Models\ConsultationType;


class CarlosMendozaObPediaScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carlos = User::where('first_name', 'Carlos')->first();

        if (! $carlos) {
            $this->command?->warn('Carlos Antonio Mendoza not found. Seeder aborted.');
            return;
        }

        // ✅ Get OB + Pedia consultation types
        $types = ConsultationType::whereIn('code', ['ob', 'pedia'])->get()->keyBy('code');

        $ob = $types->get('ob');
        $pedia = $types->get('pedia');

        if (! $ob) {
            $this->command?->warn('ConsultationType code=ob not found.');
        }
        if (! $pedia) {
            $this->command?->warn('ConsultationType code=pedia not found.');
        }

        // Helper: create weekly schedule row (regular)
        $createRegular = function (?ConsultationType $type, int $dayOfWeek) use ($carlos) {
            if (! $type) return;

            DoctorSchedule::firstOrCreate(
                [
                    'user_id' => $carlos->id,
                    'consultation_type_id' => $type->id,
                    'schedule_type' => 'regular',
                    'day_of_week' => $dayOfWeek,
                ],
                [
                    'is_available' => true,
                    'start_time' => null, // keep null to use system default hours
                    'end_time' => null,
                ]
            );
        };

        // Helper: create exception (leave day)
        $createLeave = function (?ConsultationType $type, string $date, string $reason) use ($carlos) {
            if (! $type) return;

            DoctorSchedule::firstOrCreate(
                [
                    'user_id' => $carlos->id,
                    'consultation_type_id' => $type->id,
                    'schedule_type' => 'exception',
                    'date' => $date,
                ],
                [
                    'day_of_week' => null,
                    'is_available' => false,
                    'start_time' => null,
                    'end_time' => null,
                    'reason' => $reason,
                ]
            );
        };

        // ✅ OB schedule for Carlos: Mon, Wed, Fri
        foreach ([1, 3, 5] as $dow) {
            $createRegular($ob, $dow);
        }
        // leave example (next week)
        $createLeave($ob, now()->addDays(7)->toDateString(), 'OB leave test');

        // ✅ Pedia schedule for Carlos: Tue, Thu, Sat
        foreach ([2, 4, 6] as $dow) {
            $createRegular($pedia, $dow);
        }
        // leave example (10 days from now)
        $createLeave($pedia, now()->addDays(10)->toDateString(), 'Pedia leave test');

        $this->command?->info('Carlos Mendoza OB + Pedia schedules seeded successfully.');
    }
}
