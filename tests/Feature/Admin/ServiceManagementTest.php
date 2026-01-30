<?php

use App\Livewire\Admin\ServiceManagement;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    // Create default service categories
    $this->categories = collect([
        ['name' => 'Consultation', 'code' => 'consultation'],
        ['name' => 'Ultrasound', 'code' => 'ultrasound'],
        ['name' => 'Procedure', 'code' => 'procedure'],
    ])->map(fn ($data) => ServiceCategory::factory()->create($data));
});

it('renders the service management page for admin', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.services'))
        ->assertOk()
        ->assertSeeLivewire(ServiceManagement::class);
});

it('displays service categories in dropdown', function () {
    Livewire::actingAs($this->admin)
        ->test(ServiceManagement::class)
        ->assertSee('Consultation')
        ->assertSee('Ultrasound')
        ->assertSee('Procedure');
});

it('can create a service with database category', function () {
    $category = $this->categories->first();

    Livewire::actingAs($this->admin)
        ->test(ServiceManagement::class)
        ->call('openCreateModal')
        ->set('serviceName', 'Test Service')
        ->set('serviceCategoryId', $category->id)
        ->set('basePrice', 1000)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('services', [
        'service_name' => 'Test Service',
        'service_category_id' => $category->id,
        'category' => 'consultation',
        'base_price' => 1000,
    ]);
});

it('can filter services by category', function () {
    $consultationCategory = $this->categories->firstWhere('code', 'consultation');
    $ultrasoundCategory = $this->categories->firstWhere('code', 'ultrasound');

    Service::create([
        'service_name' => 'Consultation Fee',
        'service_category_id' => $consultationCategory->id,
        'category' => 'consultation',
        'base_price' => 500,
    ]);

    Service::create([
        'service_name' => 'Abdomen Scan',
        'service_category_id' => $ultrasoundCategory->id,
        'category' => 'ultrasound',
        'base_price' => 1500,
    ]);

    Livewire::actingAs($this->admin)
        ->test(ServiceManagement::class)
        ->set('categoryFilter', $consultationCategory->id)
        ->assertSee('Consultation Fee')
        ->assertDontSee('Abdomen Scan');
});
