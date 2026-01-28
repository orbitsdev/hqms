<?php

use App\Livewire\Cashier\Dashboard;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('cashier', 'web');
    Role::findOrCreate('patient', 'web');
    Role::findOrCreate('doctor', 'web');

    $this->cashier = User::factory()->create();
    $this->cashier->assignRole('cashier');
});

test('cashier can view dashboard', function () {
    $this->actingAs($this->cashier)
        ->get(route('cashier.dashboard'))
        ->assertOk()
        ->assertSeeLivewire(Dashboard::class);
});

test('non-cashier cannot access cashier dashboard', function () {
    $patient = User::factory()->create();
    $patient->assignRole('patient');

    $this->actingAs($patient)
        ->get(route('cashier.dashboard'))
        ->assertForbidden();
});

test('dashboard displays statistics', function () {
    Livewire::actingAs($this->cashier)
        ->test(Dashboard::class)
        ->assertSee('Pending Bills')
        ->assertSee('Processed Today')
        ->assertSee('Collected Today');
});

test('guest cannot access cashier dashboard', function () {
    $this->get(route('cashier.dashboard'))
        ->assertRedirect(route('login'));
});
