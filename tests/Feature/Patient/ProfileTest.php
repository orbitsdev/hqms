<?php

use App\Livewire\Patient\Profile;
use App\Models\PersonalInformation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->personalInfo = PersonalInformation::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

it('rejects future date of birth', function () {
    Livewire::actingAs($this->user)
        ->test(Profile::class)
        ->set('date_of_birth', now()->addDay()->format('Y-m-d'))
        ->call('save')
        ->assertHasErrors(['date_of_birth']);
});

it('accepts valid past date of birth', function () {
    Livewire::actingAs($this->user)
        ->test(Profile::class)
        ->set('date_of_birth', '1990-01-15')
        ->call('save')
        ->assertHasNoErrors(['date_of_birth']);
});

it('accepts today as date of birth', function () {
    Livewire::actingAs($this->user)
        ->test(Profile::class)
        ->set('date_of_birth', now()->format('Y-m-d'))
        ->call('save')
        ->assertHasNoErrors(['date_of_birth']);
});
