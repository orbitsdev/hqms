<?php

use App\Models\ConsultationType;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('all-services display renders without auth', function () {
    $this->get(route('display.all'))
        ->assertOk()
        ->assertSee('All Services');
});

test('type-specific display renders without auth', function () {
    $type = ConsultationType::factory()->create();

    $this->get(route('display.type', $type->id))
        ->assertOk()
        ->assertSee($type->name);
});

test('back button visible for authenticated users', function () {
    Role::findOrCreate('nurse', 'web');
    $user = User::factory()->create();
    $user->assignRole('nurse');

    $this->actingAs($user)
        ->get(route('display.all'))
        ->assertOk()
        ->assertSee(route('queue-display.select'));
});

test('back button hidden for guests', function () {
    $this->get(route('display.all'))
        ->assertOk()
        ->assertDontSee(route('queue-display.select'));
});
