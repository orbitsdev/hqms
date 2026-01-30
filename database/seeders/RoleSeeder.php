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
        // Create roles (findOrCreate prevents duplicate errors)
        Role::findOrCreate('patient', 'web');
        Role::findOrCreate('nurse', 'web');
        Role::findOrCreate('doctor', 'web');
        Role::findOrCreate('cashier', 'web');
        Role::findOrCreate('admin', 'web');

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
