<?php

use App\Livewire\Display\QueueDisplaySelector;
use App\Models\ConsultationType;
use App\Models\Queue;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('patient', 'web');
    Role::findOrCreate('doctor', 'web');
    Role::findOrCreate('nurse', 'web');
    Role::findOrCreate('cashier', 'web');
    Role::findOrCreate('admin', 'web');
});

test('guest cannot access queue display selector', function () {
    $this->get(route('queue-display.select'))
        ->assertRedirect(route('login'));
});

test('authenticated user can access queue display selector', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('queue-display.select'))
        ->assertOk()
        ->assertSee('Queue Display');
})->with(['patient', 'doctor', 'nurse', 'cashier', 'admin']);

test('selector displays active consultation types', function () {
    $user = User::factory()->create();
    $user->assignRole('patient');

    $activeType = ConsultationType::factory()->create(['name' => 'Obstetrics']);
    ConsultationType::factory()->inactive()->create(['name' => 'Dermatology']);

    Livewire::actingAs($user)
        ->test(QueueDisplaySelector::class)
        ->assertSee('Obstetrics')
        ->assertDontSee('Dermatology');
});

test('selector shows waiting and serving counts', function () {
    $user = User::factory()->create();
    $user->assignRole('nurse');

    $type = ConsultationType::factory()->create();

    Queue::factory()->count(3)->waiting()->create(['consultation_type_id' => $type->id]);
    Queue::factory()->count(2)->serving()->create(['consultation_type_id' => $type->id]);

    Livewire::actingAs($user)
        ->test(QueueDisplaySelector::class)
        ->assertSee('3 waiting')
        ->assertSee('2 serving');
});

test('selector contains correct display route links', function () {
    $user = User::factory()->create();
    $user->assignRole('patient');

    $type = ConsultationType::factory()->create();

    Livewire::actingAs($user)
        ->test(QueueDisplaySelector::class)
        ->assertSeeHtml(route('display.all'))
        ->assertSeeHtml(route('display.type', $type->id));
});

test('selector shows all services card', function () {
    $user = User::factory()->create();
    $user->assignRole('patient');

    Livewire::actingAs($user)
        ->test(QueueDisplaySelector::class)
        ->assertSee('All Services');
});
