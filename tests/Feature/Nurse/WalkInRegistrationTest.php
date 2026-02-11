<?php

use App\Livewire\Nurse\WalkInRegistration;
use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\PersonalInformation;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('nurse', 'web');

    $this->nurse = User::factory()->create();
    $this->nurse->assignRole('nurse');

    PersonalInformation::factory()->create([
        'user_id' => $this->nurse->id,
        'first_name' => 'Test',
        'last_name' => 'Nurse',
    ]);

    $this->consultationType = ConsultationType::factory()->create([
        'code' => 'pedia',
        'name' => 'Pediatrics',
        'short_name' => 'P',
    ]);
});

it('renders the walk-in registration page', function () {
    actingAs($this->nurse)
        ->get(route('nurse.walk-in'))
        ->assertSuccessful()
        ->assertSeeLivewire(WalkInRegistration::class);
});

it('defaults visit type to new', function () {
    Livewire::actingAs($this->nurse)
        ->test(WalkInRegistration::class)
        ->assertSet('visitType', 'new');
});

it('can register a walk-in patient with visit type', function (string $visitType) {
    Livewire::actingAs($this->nurse)
        ->test(WalkInRegistration::class)
        ->call('selectConsultationType', $this->consultationType->id)
        ->call('nextStep')
        ->set('patientFirstName', 'Maria')
        ->set('patientLastName', 'Santos')
        ->set('patientDateOfBirth', '1990-05-15')
        ->set('patientGender', 'female')
        ->call('nextStep')
        ->set('visitType', $visitType)
        ->set('chiefComplaints', 'Patient has fever and headache')
        ->call('nextStep')
        ->call('register')
        ->assertRedirect(route('nurse.appointments', ['status' => 'pending']));

    $appointment = Appointment::where('patient_first_name', 'Maria')
        ->where('patient_last_name', 'Santos')
        ->first();

    expect($appointment)->not->toBeNull();
    expect($appointment->visit_type)->toBe($visitType);
    expect($appointment->source)->toBe('walk-in');
})->with(['new', 'old', 'revisit']);

it('validates visit type on registration', function () {
    Livewire::actingAs($this->nurse)
        ->test(WalkInRegistration::class)
        ->set('consultationTypeId', $this->consultationType->id)
        ->set('patientFirstName', 'Maria')
        ->set('patientLastName', 'Santos')
        ->set('patientDateOfBirth', '1990-05-15')
        ->set('patientGender', 'female')
        ->set('chiefComplaints', 'Patient has fever and headache')
        ->set('visitType', 'invalid')
        ->call('register')
        ->assertHasErrors(['visitType']);
});
