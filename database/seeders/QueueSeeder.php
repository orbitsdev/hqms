<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates sample queue entries for nurse/doctor module testing:
     * - Waiting queues (patients in line)
     * - One currently serving
     * - Some completed queues (history)
     */
    public function run(): void
    {
        // Get patients
        $patient1 = User::where('email', 'patient@hqms.test')->first();
        $patient2 = User::where('email', 'ana.parent@hqms.test')->first();
        $patient3 = User::where('email', 'juan.patient@hqms.test')->first();

        // Get doctors
        $obDoctor = User::where('email', 'dr.santos@hqms.test')->first();
        $pedDoctor = User::where('email', 'dr.reyes@hqms.test')->first();
        $genDoctor = User::where('email', 'dr.garcia@hqms.test')->first();

        // Get consultation types
        $obType = ConsultationType::where('code', 'ob')->first();
        $pedType = ConsultationType::where('code', 'pedia')->first();
        $genType = ConsultationType::where('code', 'general')->first();

        if (! $patient1 || ! $obType) {
            $this->command->warn('Required users or consultation types not found. Run UserSeeder and ConsultationTypeSeeder first.');

            return;
        }

        // Get approved appointments for today to create queues
        $todayApprovedAppointments = Appointment::where('appointment_date', today())
            ->where('status', 'approved')
            ->get();

        // Track queue numbers per consultation type for uniqueness and realistic numbering
        $queueNumbers = [
            $obType?->id => 1,
            $pedType?->id => 1,
            $genType?->id => 1,
        ];

        // === CREATE QUEUES FROM TODAY'S APPROVED APPOINTMENTS ===
        foreach ($todayApprovedAppointments as $appointment) {
            $queueNumber = $queueNumbers[$appointment->consultation_type_id] ?? 1;

            Queue::create([
                'appointment_id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'consultation_type_id' => $appointment->consultation_type_id,
                'doctor_id' => $appointment->doctor_id,
                'queue_number' => $queueNumber,
                'queue_date' => today(),
                'estimated_time' => $appointment->appointment_time,
                'priority' => 'normal',
                'status' => 'waiting',
                'source' => $appointment->source,
            ]);

            $queueNumbers[$appointment->consultation_type_id] = $queueNumber + 1;
        }

        // === ADD WALK-IN QUEUES FOR MORE TESTING ===
        // Walk-in OB patient
        Queue::create([
            'user_id' => $patient1->id,
            'consultation_type_id' => $obType->id,
            'doctor_id' => $obDoctor?->id,
            'queue_number' => $queueNumbers[$obType->id]++,
            'queue_date' => today(),
            'estimated_time' => now()->addHours(1)->format('H:i'),
            'priority' => 'normal',
            'status' => 'waiting',
            'source' => 'walk-in',
        ]);

        // Walk-in PEDIA patient (urgent)
        if ($patient2 && $pedType) {
            Queue::create([
                'user_id' => $patient2->id,
                'consultation_type_id' => $pedType->id,
                'doctor_id' => $pedDoctor?->id,
                'queue_number' => $queueNumbers[$pedType->id]++,
                'queue_date' => today(),
                'estimated_time' => now()->addMinutes(30)->format('H:i'),
                'priority' => 'urgent',
                'status' => 'waiting',
                'source' => 'walk-in',
            ]);
        }

        // === CREATE ONE COMPLETED QUEUE (History) ===
        Queue::create([
            'user_id' => $patient1->id,
            'consultation_type_id' => $obType->id,
            'doctor_id' => $obDoctor?->id,
            'queue_number' => 1,
            'queue_date' => today()->subDay(),
            'estimated_time' => '08:00',
            'priority' => 'normal',
            'status' => 'completed',
            'source' => 'online',
            'called_at' => today()->subDay()->setTime(8, 5),
            'serving_started_at' => today()->subDay()->setTime(8, 10),
            'serving_ended_at' => today()->subDay()->setTime(8, 30),
        ]);

        $totalQueues = Queue::whereDate('queue_date', today())->count();
        $this->command->info("Created {$totalQueues} queues for today + 1 completed from yesterday");
    }
}
