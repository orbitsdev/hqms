<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates sample appointments for nurse module testing:
     * - Pending appointments (for approval workflow)
     * - Approved appointments for today (for queue creation)
     * - Past appointments (history)
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

        // === PENDING APPOINTMENTS (For nurse to approve) ===
        // Patient 1 - OB appointment tomorrow
        Appointment::create([
            'user_id' => $patient1->id,
            'consultation_type_id' => $obType->id,
            'doctor_id' => $obDoctor?->id,
            'patient_first_name' => 'Maria',
            'patient_middle_name' => 'Isabel',
            'patient_last_name' => 'Gonzales',
            'patient_date_of_birth' => '1995-06-15',
            'patient_gender' => 'female',
            'patient_phone' => '09171111111',
            'appointment_date' => today()->addDay(),
            'appointment_time' => '09:00',
            'chief_complaints' => 'Prenatal checkup, 20 weeks pregnant',
            'status' => 'pending',
            'source' => 'online',
        ]);

        // Patient 2 - PEDIA appointment for child (pending)
        if ($patient2 && $pedType) {
            Appointment::create([
                'user_id' => $patient2->id,
                'consultation_type_id' => $pedType->id,
                'doctor_id' => $pedDoctor?->id,
                'patient_first_name' => 'Miguel',
                'patient_last_name' => 'Reyes',
                'patient_date_of_birth' => '2020-03-15',
                'patient_gender' => 'male',
                'patient_phone' => '09172222222',
                'appointment_date' => today()->addDays(2),
                'appointment_time' => '10:00',
                'chief_complaints' => 'Fever for 2 days, cough',
                'status' => 'pending',
                'source' => 'online',
            ]);
        }

        // Patient 3 - General appointment (pending)
        if ($patient3 && $genType) {
            Appointment::create([
                'user_id' => $patient3->id,
                'consultation_type_id' => $genType->id,
                'doctor_id' => $genDoctor?->id,
                'patient_first_name' => 'Juan',
                'patient_last_name' => 'Dela Cruz',
                'patient_date_of_birth' => '1970-12-05',
                'patient_gender' => 'male',
                'patient_phone' => '09173333333',
                'appointment_date' => today()->addDay(),
                'appointment_time' => '14:00',
                'chief_complaints' => 'Routine checkup, blood pressure monitoring',
                'status' => 'pending',
                'source' => 'online',
            ]);
        }

        // === APPROVED APPOINTMENTS FOR TODAY (For queue management) ===
        Appointment::create([
            'user_id' => $patient1->id,
            'consultation_type_id' => $obType->id,
            'doctor_id' => $obDoctor?->id,
            'patient_first_name' => 'Maria',
            'patient_middle_name' => 'Isabel',
            'patient_last_name' => 'Gonzales',
            'patient_date_of_birth' => '1995-06-15',
            'patient_gender' => 'female',
            'patient_phone' => '09171111111',
            'appointment_date' => today(),
            'appointment_time' => '08:30',
            'chief_complaints' => 'Follow-up prenatal visit',
            'status' => 'approved',
            'approved_at' => now()->subDay(),
            'source' => 'online',
        ]);

        if ($patient2 && $pedType) {
            Appointment::create([
                'user_id' => $patient2->id,
                'consultation_type_id' => $pedType->id,
                'doctor_id' => $pedDoctor?->id,
                'patient_first_name' => 'Sofia',
                'patient_last_name' => 'Reyes',
                'patient_date_of_birth' => '2018-07-22',
                'patient_gender' => 'female',
                'patient_phone' => '09172222222',
                'appointment_date' => today(),
                'appointment_time' => '09:30',
                'chief_complaints' => 'Vaccination schedule - MMR',
                'status' => 'approved',
                'approved_at' => now()->subDay(),
                'source' => 'online',
            ]);
        }

        if ($patient3 && $genType) {
            Appointment::create([
                'user_id' => $patient3->id,
                'consultation_type_id' => $genType->id,
                'doctor_id' => $genDoctor?->id,
                'patient_first_name' => 'Juan',
                'patient_last_name' => 'Dela Cruz',
                'patient_date_of_birth' => '1970-12-05',
                'patient_gender' => 'male',
                'patient_phone' => '09173333333',
                'appointment_date' => today(),
                'appointment_time' => '10:00',
                'chief_complaints' => 'Diabetic monitoring, blood sugar check',
                'status' => 'approved',
                'approved_at' => now()->subDay(),
                'source' => 'online',
            ]);
        }

        // === PAST COMPLETED APPOINTMENTS (History) ===
        Appointment::create([
            'user_id' => $patient1->id,
            'consultation_type_id' => $obType->id,
            'doctor_id' => $obDoctor?->id,
            'patient_first_name' => 'Maria',
            'patient_middle_name' => 'Isabel',
            'patient_last_name' => 'Gonzales',
            'patient_date_of_birth' => '1995-06-15',
            'patient_gender' => 'female',
            'patient_phone' => '09171111111',
            'appointment_date' => today()->subWeek(),
            'appointment_time' => '09:00',
            'chief_complaints' => 'Initial prenatal consultation',
            'status' => 'completed',
            'approved_at' => today()->subWeeks(2),
            'source' => 'online',
        ]);

        $this->command->info('Created sample appointments: 3 pending, 3 approved for today, 1 completed');
    }
}
