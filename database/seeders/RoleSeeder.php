<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
       Role::create(['name' => 'patient']);
        Role::create(['name' => 'nurse']);
        Role::create(['name' => 'doctor']);
        Role::create(['name' => 'cashier']);
        Role::create(['name' => 'admin']);

        // TODO: Permissions will be added later
        // $permissions = [
        //     // Appointments
        //     'view-appointments',
        //     'create-appointments',
        //     'approve-appointments',
        //     'decline-appointments',
        //     'cancel-appointments',
        //
        //     // Queue
        //     'manage-queue',
        //     'call-queue',
        //     'skip-queue',
        //
        //     // Medical
        //     'input-vital-signs',
        //     'view-medical-records',
        //     'add-diagnosis',
        //     'add-prescription',
        //
        //     // Billing
        //     'view-billing',
        //     'process-billing',
        //     'apply-discount',
        //
        //     // Admission
        //     'admit-patient',
        //     'discharge-patient',
        //
        //     // Reports
        //     'view-reports',
        //
        //     // System
        //     'manage-users',
        //     'manage-system-settings',
        //     'manage-displays',
        // ];
        //
        // foreach ($permissions as $permission) {
        //     Permission::create(['name' => $permission]);
        // }
        //
        // // Assign permissions to roles
        // $nurse->givePermissionTo([...]);
        // $doctor->givePermissionTo([...]);
        // $cashier->givePermissionTo([...]);
        // $admin->givePermissionTo(Permission::all());
    }
}
