<?php

use App\Livewire\Admin\UserManagement;
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

test('admin can view user management page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users'))
        ->assertOk()
        ->assertSeeLivewire(UserManagement::class);
});

test('non-admin cannot access user management', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $this->actingAs($nurse)
        ->get(route('admin.users'))
        ->assertForbidden();
});

test('can search users', function () {
    $user = User::factory()->create(['email' => 'searchable@test.com']);
    $user->assignRole('patient');

    Livewire::actingAs($this->admin)
        ->test(UserManagement::class)
        ->set('search', 'searchable')
        ->assertSee('searchable@test.com');
});

test('can filter by role', function () {
    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    $patient = User::factory()->create();
    $patient->assignRole('patient');

    Livewire::actingAs($this->admin)
        ->test(UserManagement::class)
        ->set('roleFilter', 'doctor')
        ->assertSee($doctor->email)
        ->assertDontSee($patient->email);
});

test('can create new user', function () {
    Livewire::actingAs($this->admin)
        ->test(UserManagement::class)
        ->call('openCreateModal')
        ->assertSet('showUserModal', true)
        ->set('email', 'newuser@test.com')
        ->set('password', 'Password123!')
        ->set('passwordConfirmation', 'Password123!')
        ->set('role', 'patient')
        ->set('firstName', 'New')
        ->set('lastName', 'User')
        ->call('saveUser')
        ->assertSet('showUserModal', false);

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@test.com',
        'first_name' => 'New',
        'last_name' => 'User',
    ]);
});

test('can create user with 6 character password', function () {
    Livewire::actingAs($this->admin)
        ->test(UserManagement::class)
        ->call('openCreateModal')
        ->set('email', 'shortpw@test.com')
        ->set('password', 'abc123')
        ->set('passwordConfirmation', 'abc123')
        ->set('role', 'nurse')
        ->set('firstName', 'Short')
        ->set('lastName', 'Password')
        ->call('saveUser')
        ->assertHasNoErrors('password');

    $this->assertDatabaseHas('users', [
        'email' => 'shortpw@test.com',
    ]);
});

test('rejects password shorter than 6 characters', function () {
    Livewire::actingAs($this->admin)
        ->test(UserManagement::class)
        ->call('openCreateModal')
        ->set('email', 'tooshort@test.com')
        ->set('password', 'ab123')
        ->set('passwordConfirmation', 'ab123')
        ->set('role', 'nurse')
        ->set('firstName', 'Too')
        ->set('lastName', 'Short')
        ->call('saveUser')
        ->assertHasErrors('password');
});

test('can deactivate user with soft delete', function () {
    $user = User::factory()->create();
    $user->assignRole('patient');

    Livewire::actingAs($this->admin)
        ->test(UserManagement::class)
        ->call('openDeleteModal', $user->id)
        ->assertSet('showDeleteModal', true)
        ->call('deleteUser')
        ->assertSet('showDeleteModal', false);

    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

test('can restore soft deleted user', function () {
    $user = User::factory()->create();
    $user->assignRole('patient');
    $user->delete();

    Livewire::actingAs($this->admin)
        ->test(UserManagement::class)
        ->set('statusFilter', 'inactive')
        ->call('restoreUser', $user->id);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'deleted_at' => null,
    ]);
});

test('can switch between active and inactive users', function () {
    $activeUser = User::factory()->create();
    $activeUser->assignRole('patient');

    $inactiveUser = User::factory()->create();
    $inactiveUser->assignRole('patient');
    $inactiveUser->delete();

    Livewire::actingAs($this->admin)
        ->test(UserManagement::class)
        ->assertSet('statusFilter', 'active')
        ->assertSee($activeUser->email)
        ->assertDontSee($inactiveUser->email)
        ->set('statusFilter', 'inactive')
        ->assertSee($inactiveUser->email)
        ->assertDontSee($activeUser->email);
});
