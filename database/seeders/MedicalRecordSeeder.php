<?php

namespace Database\Seeders;

use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicalRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users and consultation types
        $patient1 = User::where('email', 'patient@hqms.test')->first();
        $patient2 = User::where('email', 'ana.parent@hqms.test')->first();
        $patient3 = User::where('email', 'juan.patient@hqms.test')->first();

        $obDoctor = User::where('email', 'dr.santos@hqms.test')->first();
        $pedDoctor = User::where('email', 'dr.reyes@hqms.test')->first();
        $genDoctor = User::where('email', 'dr.garcia@hqms.test')->first();

        $nurse = User::where('email', 'nurse@hqms.test')->first();

        $obType = ConsultationType::where('code', 'ob')->first();
        $pedType = ConsultationType::where('code', 'pedia')->first();
        $genType = ConsultationType::where('code', 'general')->first();

        $recordNumber = static fn (int $sequence): string => sprintf('MR-%d-%05d', now()->year, $sequence);

        if (! $patient1 || ! $obDoctor || ! $nurse) {
            $this->command->warn('Required users not found. Run UserSeeder first.');

            return;
        }

        // === PATIENT 1 (Maria Gonzales) - OB Records ===
        // Record 1: Completed OB visit (2 weeks ago)
        $record1TimeIn = now()->subDays(14)->setTime(9, 0);
        $record1ExamStart = now()->subDays(14)->setTime(9, 30);

        $record1 = MedicalRecord::create([
            'record_number' => $recordNumber(1),
            'user_id' => $patient1->id,
            'consultation_type_id' => $obType->id,
            'doctor_id' => $obDoctor->id,
            'nurse_id' => $nurse->id,

            'patient_first_name' => 'Maria',
            'patient_middle_name' => 'Isabel',
            'patient_last_name' => 'Gonzales',
            'patient_date_of_birth' => '1995-06-15',
            'patient_gender' => 'female',
            'patient_marital_status' => 'married',
            'patient_province' => 'Sultan Kudarat',
            'patient_municipality' => 'Tacurong City',
            'patient_barangay' => 'Poblacion',
            'patient_street' => '123 Bonifacio St.',
            'patient_contact_number' => '09171111111',
            'patient_occupation' => 'Teacher',
            'patient_blood_type' => 'O+',
            'emergency_contact_name' => 'Jose Gonzales',
            'emergency_contact_phone' => '09171111112',

            'visit_date' => $record1TimeIn->toDateString(),
            'time_in' => $record1TimeIn,
            'time_in_period' => $record1TimeIn->format('a') === 'am' ? 'am' : 'pm',
            'visit_type' => 'old',
            'service_type' => 'checkup',
            'ob_type' => 'prenatal',
            'service_category' => 'non-surgical',

            'chief_complaints_initial' => 'Prenatal checkup, 28 weeks pregnant',
            'chief_complaints_updated' => 'Prenatal checkup, 28 weeks AOG. No complaints.',

            'temperature' => 36.5,
            'blood_pressure' => '110/70',
            'cardiac_rate' => 78,
            'respiratory_rate' => 18,
            'weight' => 62.5,
            'height' => 158.0,
            'fetal_heart_tone' => 145,
            'fundal_height' => 28.0,
            'last_menstrual_period' => now()->subWeeks(28)->format('Y-m-d'),
            'vital_signs_recorded_at' => $record1TimeIn->copy()->addMinutes(15),

            'pertinent_hpi_pe' => 'G2P1 (1001), 28 weeks AOG. No vaginal bleeding, no abdominal pain, good fetal movement. BP stable, no edema.',
            'diagnosis' => 'Pregnancy Uterine 28 weeks AOG, G2P1',
            'plan' => 'Continue prenatal vitamins. Return in 2 weeks. Advice on danger signs.',
            'examined_at' => $record1ExamStart,
            'examination_ended_at' => $record1ExamStart->copy()->addMinutes(25),
            'examination_time' => 'am',

            'status' => 'completed',
        ]);

        // Prescriptions for record 1
        Prescription::create([
            'medical_record_id' => $record1->id,
            'prescribed_by' => $obDoctor->id,
            'medication_name' => 'Ferrous Sulfate',
            'dosage' => '325mg',
            'frequency' => 'once daily',
            'duration' => '30 days',
            'instructions' => 'Take after meals',
            'quantity' => 30,
            'is_hospital_drug' => false,
        ]);

        Prescription::create([
            'medical_record_id' => $record1->id,
            'prescribed_by' => $obDoctor->id,
            'medication_name' => 'Folic Acid',
            'dosage' => '5mg',
            'frequency' => 'once daily',
            'duration' => '30 days',
            'instructions' => 'Take in the morning',
            'quantity' => 30,
            'is_hospital_drug' => false,
        ]);

        // Record 2: Completed OB visit (4 weeks ago)
        $record2TimeIn = now()->subDays(28)->setTime(10, 30);
        $record2ExamStart = now()->subDays(28)->setTime(11, 0);

        $record2 = MedicalRecord::create([
            'record_number' => $recordNumber(2),
            'user_id' => $patient1->id,
            'consultation_type_id' => $obType->id,
            'doctor_id' => $obDoctor->id,
            'nurse_id' => $nurse->id,

            'patient_first_name' => 'Maria',
            'patient_middle_name' => 'Isabel',
            'patient_last_name' => 'Gonzales',
            'patient_date_of_birth' => '1995-06-15',
            'patient_gender' => 'female',
            'patient_marital_status' => 'married',
            'patient_province' => 'Sultan Kudarat',
            'patient_municipality' => 'Tacurong City',
            'patient_barangay' => 'Poblacion',
            'patient_street' => '123 Bonifacio St.',
            'patient_contact_number' => '09171111111',
            'patient_occupation' => 'Teacher',
            'patient_blood_type' => 'O+',
            'emergency_contact_name' => 'Jose Gonzales',
            'emergency_contact_phone' => '09171111112',

            'visit_date' => $record2TimeIn->toDateString(),
            'time_in' => $record2TimeIn,
            'time_in_period' => $record2TimeIn->format('a') === 'am' ? 'am' : 'pm',
            'visit_type' => 'old',
            'service_type' => 'checkup',
            'ob_type' => 'prenatal',
            'service_category' => 'non-surgical',

            'chief_complaints_initial' => 'Prenatal checkup, 26 weeks pregnant',

            'temperature' => 36.8,
            'blood_pressure' => '120/80',
            'cardiac_rate' => 80,
            'respiratory_rate' => 16,
            'weight' => 61.0,
            'height' => 158.0,
            'fetal_heart_tone' => 142,
            'fundal_height' => 26.0,
            'last_menstrual_period' => now()->subWeeks(30)->format('Y-m-d'),
            'vital_signs_recorded_at' => $record2TimeIn->copy()->addMinutes(10),

            'pertinent_hpi_pe' => 'G2P1 (1001), 26 weeks AOG. Normal prenatal course.',
            'diagnosis' => 'Pregnancy Uterine 26 weeks AOG',
            'plan' => 'Continue prenatal vitamins. Ultrasound scheduled.',
            'examined_at' => $record2ExamStart,
            'examination_ended_at' => $record2ExamStart->copy()->addMinutes(20),
            'examination_time' => 'am',

            'status' => 'completed',
        ]);

        // === PATIENT 2 (Ana Reyes) - PEDIA Records for her child ===
        if ($patient2 && $pedDoctor && $pedType) {
            $record3TimeIn = now()->subDays(7)->setTime(14, 15);
            $record3ExamStart = now()->subDays(7)->setTime(14, 40);

            $record3 = MedicalRecord::create([
                'record_number' => $recordNumber(3),
                'user_id' => $patient2->id,
                'consultation_type_id' => $pedType->id,
                'doctor_id' => $pedDoctor->id,
                'nurse_id' => $nurse->id,

                'patient_first_name' => 'Miguel',
                'patient_last_name' => 'Reyes',
                'patient_date_of_birth' => '2020-03-15',
                'patient_gender' => 'male',
                'patient_marital_status' => 'child',
                'patient_province' => 'Sultan Kudarat',
                'patient_municipality' => 'Tacurong City',
                'patient_barangay' => 'New Isabela',
                'patient_street' => '456 Rizal Ave.',
                'patient_contact_number' => '09172222222',
                'emergency_contact_name' => 'Ana Reyes',
                'emergency_contact_phone' => '09172222222',

                'visit_date' => $record3TimeIn->toDateString(),
                'time_in' => $record3TimeIn,
                'time_in_period' => $record3TimeIn->format('a') === 'am' ? 'am' : 'pm',
                'visit_type' => 'new',
                'service_type' => 'checkup',
                'service_category' => 'non-surgical',

                'chief_complaints_initial' => 'Fever and cough for 2 days',
                'chief_complaints_updated' => 'Fever (38.5C) and productive cough for 2 days. No difficulty breathing.',

                'temperature' => 38.5,
                'blood_pressure' => '90/60',
                'cardiac_rate' => 110,
                'respiratory_rate' => 28,
                'weight' => 14.5,
                'height' => 92.0,
                'head_circumference' => 49.0,
                'chest_circumference' => 52.0,
                'vital_signs_recorded_at' => $record3TimeIn->copy()->addMinutes(10),

                'pertinent_hpi_pe' => '4-year-old male with 2-day history of fever and cough. Throat: pharyngeal congestion. Lungs: clear breath sounds bilaterally.',
                'diagnosis' => 'Upper Respiratory Tract Infection (URTI)',
                'plan' => 'Symptomatic treatment. Increase fluid intake. Return if fever persists beyond 3 days.',
                'examined_at' => $record3ExamStart,
                'examination_ended_at' => $record3ExamStart->copy()->addMinutes(15),
                'examination_time' => 'pm',

                'status' => 'completed',
            ]);

            Prescription::create([
                'medical_record_id' => $record3->id,
                'prescribed_by' => $pedDoctor->id,
                'medication_name' => 'Paracetamol Syrup',
                'dosage' => '250mg/5ml',
                'frequency' => 'every 4-6 hours as needed for fever',
                'duration' => '3 days',
                'instructions' => 'Give 5ml for fever above 37.5C',
                'quantity' => 1,
                'is_hospital_drug' => false,
            ]);

            Prescription::create([
                'medical_record_id' => $record3->id,
                'prescribed_by' => $pedDoctor->id,
                'medication_name' => 'Ambroxol Syrup',
                'dosage' => '15mg/5ml',
                'frequency' => '3x daily',
                'duration' => '5 days',
                'instructions' => 'Give 2.5ml three times a day',
                'quantity' => 1,
                'is_hospital_drug' => false,
            ]);
        }

        // === PATIENT 3 (Juan Dela Cruz) - General Medicine ===
        if ($patient3 && $genDoctor && $genType) {
            $record4TimeIn = now()->subDays(3)->setTime(8, 45);
            $record4ExamStart = now()->subDays(3)->setTime(9, 15);

            $record4 = MedicalRecord::create([
                'record_number' => $recordNumber(4),
                'user_id' => $patient3->id,
                'consultation_type_id' => $genType->id,
                'doctor_id' => $genDoctor->id,
                'nurse_id' => $nurse->id,

                'patient_first_name' => 'Juan',
                'patient_last_name' => 'Dela Cruz',
                'patient_date_of_birth' => '1970-12-05',
                'patient_gender' => 'male',
                'patient_marital_status' => 'married',
                'patient_province' => 'Sultan Kudarat',
                'patient_municipality' => 'Tacurong City',
                'patient_barangay' => 'San Emmanuel',
                'patient_street' => '789 Mabini St.',
                'patient_contact_number' => '09173333333',
                'patient_occupation' => 'Farmer',
                'patient_blood_type' => 'B+',
                'patient_chronic_conditions' => 'Hypertension',
                'emergency_contact_name' => 'Maria Dela Cruz',
                'emergency_contact_phone' => '09173333334',

                'visit_date' => $record4TimeIn->toDateString(),
                'time_in' => $record4TimeIn,
                'time_in_period' => $record4TimeIn->format('a') === 'am' ? 'am' : 'pm',
                'visit_type' => 'old',
                'service_type' => 'checkup',
                'service_category' => 'non-surgical',

                'chief_complaints_initial' => 'Follow-up for hypertension',
                'chief_complaints_updated' => 'Hypertension follow-up. Compliant with medications.',

                'temperature' => 36.7,
                'blood_pressure' => '140/90',
                'cardiac_rate' => 72,
                'respiratory_rate' => 16,
                'weight' => 75.0,
                'height' => 168.0,
                'vital_signs_recorded_at' => $record4TimeIn->copy()->addMinutes(10),

                'pertinent_hpi_pe' => '54-year-old male, known hypertensive for 5 years. Compliant with medications. No chest pain, no headache, no blurring of vision.',
                'diagnosis' => 'Essential Hypertension, controlled',
                'plan' => 'Continue current medications. Lifestyle modification. Return in 1 month.',
                'examined_at' => $record4ExamStart,
                'examination_ended_at' => $record4ExamStart->copy()->addMinutes(15),
                'examination_time' => 'am',

                'status' => 'completed',
            ]);

            Prescription::create([
                'medical_record_id' => $record4->id,
                'prescribed_by' => $genDoctor->id,
                'medication_name' => 'Losartan',
                'dosage' => '50mg',
                'frequency' => 'once daily',
                'duration' => '30 days',
                'instructions' => 'Take in the morning',
                'quantity' => 30,
                'is_hospital_drug' => false,
            ]);

            Prescription::create([
                'medical_record_id' => $record4->id,
                'prescribed_by' => $genDoctor->id,
                'medication_name' => 'Amlodipine',
                'dosage' => '5mg',
                'frequency' => 'once daily',
                'duration' => '30 days',
                'instructions' => 'Take in the evening',
                'quantity' => 30,
                'is_hospital_drug' => false,
            ]);
        }

        $this->command->info('Medical records seeded successfully!');
    }
}
