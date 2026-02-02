<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Roles & Permissions (must be first)
            RoleSeeder::class,

            // 2. Consultation Types (needed for users and displays)
            ConsultationTypeSeeder::class,

            // 3. Users (depends on roles and consultation types)
            UserSeeder::class,

            // 4. Doctor Schedules (depends on users and consultation types)
            DoctorScheduleSeeder::class,

            // 5. Service Categories, Services & Drugs
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            HospitalDrugSeeder::class,

            // 6. Discounts (for billing)
            DiscountSeeder::class,

            // 7. System Settings (independent)
            SystemSettingSeeder::class,

            // 8. Queue Displays (depends on consultation types)
            QueueDisplaySeeder::class,

            // 9. Test Data: Appointments and Queues (for nurse/doctor module testing)
            AppointmentSeeder::class,
            QueueSeeder::class,
            CarlosMendozaObPediaScheduleSeeder::class,
        ]);
    }
}
