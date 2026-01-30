<?php

use App\Livewire\Admin\DiscountManagement;
use App\Models\Discount;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('patient', 'web');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('admin can view discount management page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.discounts'))
        ->assertOk()
        ->assertSeeLivewire(DiscountManagement::class);
});

it('non-admin cannot access discount management', function () {
    $user = User::factory()->create();
    $user->assignRole('patient');

    $this->actingAs($user)
        ->get(route('admin.discounts'))
        ->assertForbidden();
});

it('displays existing discounts', function () {
    Discount::factory()->create(['name' => 'Test Discount', 'code' => 'test', 'percentage' => 15]);

    Livewire::actingAs($this->admin)
        ->test(DiscountManagement::class)
        ->assertSee('Test Discount')
        ->assertSee('15%');
});

it('can create new discount', function () {
    Livewire::actingAs($this->admin)
        ->test(DiscountManagement::class)
        ->call('openCreateModal')
        ->set('name', 'New Discount')
        ->set('code', 'new-discount')
        ->set('percentage', 25)
        ->set('description', 'Test description')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('discounts', [
        'name' => 'New Discount',
        'code' => 'new-discount',
        'percentage' => 25,
    ]);
});

it('can edit existing discount', function () {
    $discount = Discount::factory()->create(['name' => 'Old Name', 'percentage' => 10]);

    Livewire::actingAs($this->admin)
        ->test(DiscountManagement::class)
        ->call('openEditModal', $discount->id)
        ->set('name', 'Updated Name')
        ->set('percentage', 20)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('discounts', [
        'id' => $discount->id,
        'name' => 'Updated Name',
        'percentage' => 20,
    ]);
});

it('can toggle discount active status', function () {
    $discount = Discount::factory()->create(['is_active' => true]);

    Livewire::actingAs($this->admin)
        ->test(DiscountManagement::class)
        ->call('toggleActive', $discount->id);

    $this->assertFalse($discount->fresh()->is_active);
});

it('can delete discount', function () {
    $discount = Discount::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(DiscountManagement::class)
        ->call('delete', $discount->id);

    $this->assertDatabaseMissing('discounts', ['id' => $discount->id]);
});

it('can search discounts', function () {
    Discount::factory()->create(['name' => 'Senior Citizen']);
    Discount::factory()->create(['name' => 'PWD Discount']);

    Livewire::actingAs($this->admin)
        ->test(DiscountManagement::class)
        ->set('search', 'Senior')
        ->assertSee('Senior Citizen')
        ->assertDontSee('PWD Discount');
});

it('validates required fields', function () {
    Livewire::actingAs($this->admin)
        ->test(DiscountManagement::class)
        ->call('openCreateModal')
        ->set('name', '')
        ->set('code', '')
        ->call('save')
        ->assertHasErrors(['name', 'code']);
});

it('validates percentage range', function () {
    Livewire::actingAs($this->admin)
        ->test(DiscountManagement::class)
        ->call('openCreateModal')
        ->set('name', 'Test')
        ->set('code', 'test')
        ->set('percentage', 150)
        ->call('save')
        ->assertHasErrors(['percentage']);
});
