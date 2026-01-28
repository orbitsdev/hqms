<?php

use App\Livewire\Admin\Dashboard;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('patient', 'web');
    Role::findOrCreate('doctor', 'web');
    Role::findOrCreate('nurse', 'web');
    Role::findOrCreate('cashier', 'web');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('admin can view dashboard', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeLivewire(Dashboard::class);
});

test('non-admin cannot access admin dashboard', function () {
    $patient = User::factory()->create();
    $patient->assignRole('patient');

    $this->actingAs($patient)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('dashboard displays user statistics', function () {
    User::factory()->count(3)->create()->each(fn ($u) => $u->assignRole('patient'));
    User::factory()->count(2)->create()->each(fn ($u) => $u->assignRole('doctor'));

    Livewire::actingAs($this->admin)
        ->test(Dashboard::class)
        ->assertSee('Total')
        ->assertSee('Patients')
        ->assertSee('Doctors');
});

test('guest cannot access admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});
