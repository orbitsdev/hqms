4<?php

namespace Database\Seeders;

use App\Models\ConsultationType;
use App\Models\PersonalInformation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // === ADMIN USER ===
        $admin = User::create([
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');
        PersonalInformation::create([
            'user_id' => $admin->id,
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'phone' => '09170000001',
            'gender' => 'male',
        ]);

        // === DOCTORS ===
        // OB Doctor
        $obDoctor = User::create([
            'first_name' => 'Maria',
            'middle_name' => 'Cruz',
            'last_name' => 'Santos',
            'email' => 'dr.santos@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $obDoctor->assignRole('doctor');
        PersonalInformation::create([
            'user_id' => $obDoctor->id,
            'first_name' => 'Maria',
            'middle_name' => 'Cruz',
            'last_name' => 'Santos',
            'phone' => '09170000010',
            'gender' => 'female',
            'date_of_birth' => '1980-05-15',
            'province' => 'Sultan Kudarat',
            'municipality' => 'Tacurong City',
            'occupation' => 'OB-Gynecologist',
        ]);
        // Assign OB consultation type
        $obType = ConsultationType::where('code', 'ob')->first();
        if ($obType) {
            $obDoctor->consultationTypes()->attach($obType->id);
        }

        // PEDIA Doctor
        $pedDoctor = User::create([
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Reyes',
            'email' => 'dr.reyes@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $pedDoctor->assignRole('doctor');
        PersonalInformation::create([
            'user_id' => $pedDoctor->id,
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Reyes',
            'phone' => '09170000011',
            'gender' => 'male',
            'date_of_birth' => '1975-08-20',
            'province' => 'Sultan Kudarat',
            'municipality' => 'Tacurong City',
            'occupation' => 'Pediatrician',
        ]);
        // Assign PEDIA consultation type
        $pedType = ConsultationType::where('code', 'pedia')->first();
        if ($pedType) {
            $pedDoctor->consultationTypes()->attach($pedType->id);
        }

        // General Medicine Doctor
        $genDoctor = User::create([
            'first_name' => 'Ana',
            'last_name' => 'Garcia',
            'email' => 'dr.garcia@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $genDoctor->assignRole('doctor');
        PersonalInformation::create([
            'user_id' => $genDoctor->id,
            'first_name' => 'Ana',
            'last_name' => 'Garcia',
            'phone' => '09170000012',
            'gender' => 'female',
            'date_of_birth' => '1985-03-10',
            'province' => 'Sultan Kudarat',
            'municipality' => 'Tacurong City',
            'occupation' => 'General Practitioner',
        ]);
        // Assign GENERAL consultation type
        $genType = ConsultationType::where('code', 'general')->first();
        if ($genType) {
            $genDoctor->consultationTypes()->attach($genType->id);
        }

        // Single email doctor (backward compatible with old seeder)
        $doctor = User::create([
            'first_name' => 'Carlos',
            'middle_name' => 'Antonio',
            'last_name' => 'Mendoza',
            'email' => 'doctor@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $doctor->assignRole('doctor');
        PersonalInformation::create([
            'user_id' => $doctor->id,
            'first_name' => 'Carlos',
            'middle_name' => 'Antonio',
            'last_name' => 'Mendoza',
            'phone' => '09170000013',
            'gender' => 'male',
            'date_of_birth' => '1978-11-25',
            'province' => 'Sultan Kudarat',
            'municipality' => 'Tacurong City',
            'occupation' => 'General Practitioner',
        ]);
        // Assign all consultation types
        if ($obType) {
            $doctor->consultationTypes()->attach($obType->id);
        }
        if ($pedType) {
            $doctor->consultationTypes()->attach($pedType->id);
        }
        if ($genType) {
            $doctor->consultationTypes()->attach($genType->id);
        }

        // === NURSES ===
        $nurse1 = User::create([
            'first_name' => 'Rosa',
            'last_name' => 'Cruz',
            'email' => 'nurse@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $nurse1->assignRole('nurse');
        PersonalInformation::create([
            'user_id' => $nurse1->id,
            'first_name' => 'Rosa',
            'last_name' => 'Cruz',
            'phone' => '09170000020',
            'gender' => 'female',
            'date_of_birth' => '1990-07-25',
            'occupation' => 'Registered Nurse',
        ]);

        $nurse2 = User::create([
            'first_name' => 'Carmen',
            'last_name' => 'Lopez',
            'email' => 'nurse.lopez@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $nurse2->assignRole('nurse');
        PersonalInformation::create([
            'user_id' => $nurse2->id,
            'first_name' => 'Carmen',
            'last_name' => 'Lopez',
            'phone' => '09170000021',
            'gender' => 'female',
            'date_of_birth' => '1988-11-12',
            'occupation' => 'Registered Nurse',
        ]);

        // === CASHIER ===
        $cashier = User::create([
            'first_name' => 'Pedro',
            'last_name' => 'Mendoza',
            'email' => 'cashier@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $cashier->assignRole('cashier');
        PersonalInformation::create([
            'user_id' => $cashier->id,
            'first_name' => 'Pedro',
            'last_name' => 'Mendoza',
            'phone' => '09170000030',
            'gender' => 'male',
            'date_of_birth' => '1992-04-18',
            'occupation' => 'Hospital Cashier',
        ]);

        // === SAMPLE PATIENTS ===
        // Patient 1: Adult female (for OB)
        $patient1 = User::create([
            'first_name' => 'Maria',
            'middle_name' => 'Isabel',
            'last_name' => 'Gonzales',
            'email' => 'patient@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $patient1->assignRole('patient');
        PersonalInformation::create([
            'user_id' => $patient1->id,
            'first_name' => 'Maria',
            'middle_name' => 'Isabel',
            'last_name' => 'Gonzales',
            'phone' => '09171111111',
            'gender' => 'female',
            'date_of_birth' => '1995-06-15',
            'marital_status' => 'married',
            'province' => 'Sultan Kudarat',
            'municipality' => 'Tacurong City',
            'barangay' => 'Poblacion',
            'street' => '123 Bonifacio St.',
            'occupation' => 'Teacher',
            'emergency_contact_name' => 'Jose Gonzales',
            'emergency_contact_phone' => '09171111112',
        ]);

        // Patient 2: Parent with children (for PEDIA bookings)
        $patient2 = User::create([
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'email' => 'ana.parent@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $patient2->assignRole('patient');
        PersonalInformation::create([
            'user_id' => $patient2->id,
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'phone' => '09172222222',
            'gender' => 'female',
            'date_of_birth' => '1988-09-20',
            'marital_status' => 'married',
            'province' => 'Sultan Kudarat',
            'municipality' => 'Tacurong City',
            'barangay' => 'New Isabela',
            'street' => '456 Rizal Ave.',
            'occupation' => 'Housewife',
            'emergency_contact_name' => 'Roberto Reyes',
            'emergency_contact_phone' => '09172222223',
        ]);

        // Patient 3: Adult male (for General)
        $patient3 = User::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan.patient@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $patient3->assignRole('patient');
        PersonalInformation::create([
            'user_id' => $patient3->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'phone' => '09173333333',
            'gender' => 'male',
            'date_of_birth' => '1970-12-05',
            'marital_status' => 'married',
            'province' => 'Sultan Kudarat',
            'municipality' => 'Tacurong City',
            'barangay' => 'San Emmanuel',
            'street' => '789 Mabini St.',
            'occupation' => 'Farmer',
            'emergency_contact_name' => 'Maria Dela Cruz',
            'emergency_contact_phone' => '09173333334',
        ]);

        // Patient 4: Incomplete profile (to test personal-info guard)
        $patient4 = User::create([
            'first_name' => 'Test',
            'last_name' => 'Incomplete',
            'email' => 'incomplete@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $patient4->assignRole('patient');
        // No PersonalInformation created on purpose
    }
}
