<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PersonalInformation;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        $admin = User::create([
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
            'phone' => '09171234567',
            'gender' => 'male',
        ]);

        // Doctor User
        $doctor = User::create([
            'email' => 'doctor@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $doctor->assignRole('doctor');
        PersonalInformation::create([
            'user_id' => $doctor->id,
            'first_name' => 'Maria',
            'middle_name' => 'Santos',
            'last_name' => 'Dela Cruz',
            'phone' => '09171234568',
            'gender' => 'female',
            'date_of_birth' => '1980-05-15',
        ]);

        // Nurse User
        $nurse = User::create([
            'email' => 'nurse@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $nurse->assignRole('nurse');
        PersonalInformation::create([
            'user_id' => $nurse->id,
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'phone' => '09171234569',
            'gender' => 'female',
            'date_of_birth' => '1990-08-20',
        ]);

        // Cashier User
        $cashier = User::create([
            'email' => 'cashier@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $cashier->assignRole('cashier');
        PersonalInformation::create([
            'user_id' => $cashier->id,
            'first_name' => 'Juan',
            'last_name' => 'Santos',
            'phone' => '09171234570',
            'gender' => 'male',
            'date_of_birth' => '1985-03-10',
        ]);

        // Patient User
        $patient = User::create([
            'email' => 'patient@hqms.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $patient->assignRole('patient');
        PersonalInformation::create([
            'user_id' => $patient->id,
            'first_name' => 'Rosa',
            'middle_name' => 'Garcia',
            'last_name' => 'Martinez',
            'phone' => '09171234571',
            'gender' => 'female',
            'date_of_birth' => '1995-12-01',
            'marital_status' => 'married',
            'province' => 'Laguna',
            'municipality' => 'San Pablo City',
            'barangay' => 'San Jose',
            'street' => '123 Main Street',
            'occupation' => 'Teacher',
            'emergency_contact_name' => 'Pedro Martinez',
            'emergency_contact_phone' => '09181234567',
        ]);
    }
}
