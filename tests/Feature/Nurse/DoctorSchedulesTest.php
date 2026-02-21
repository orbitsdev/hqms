<?php

use App\Livewire\Nurse\DoctorSchedules;
use App\Models\ConsultationType;
use App\Models\DoctorSchedule;
use App\Models\PersonalInformation;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Ensure roles exist
    Role::findOrCreate('nurse', 'web');
    Role::findOrCreate('doctor', 'web');
});

// ==================== ACCESS TESTS ====================

it('renders the doctor schedules page for nurses', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    actingAs($nurse)
        ->get(route('nurse.doctor-schedules'))
        ->assertOk()
        ->assertSeeLivewire(DoctorSchedules::class);
});

it('denies access to non-nurses', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('nurse.doctor-schedules'))
        ->assertForbidden();
});

// ==================== DISPLAY TESTS ====================

it('displays doctors in the dropdown', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create(['first_name' => 'John', 'last_name' => 'Smith']);
    $doctor->assignRole('doctor');
    PersonalInformation::factory()->create([
        'user_id' => $doctor->id,
        'first_name' => 'John',
        'middle_name' => null,
        'last_name' => 'Smith',
    ]);

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->assertSee('John Smith');
});

it('displays consultation types in the dropdown', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $type = ConsultationType::factory()->create(['name' => 'Pediatrics']);

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->assertSee('Pediatrics');
});

it('displays weekly schedules when in weekly view mode', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create(['first_name' => 'Jane', 'last_name' => 'Doe']);
    $doctor->assignRole('doctor');
    PersonalInformation::factory()->create([
        'user_id' => $doctor->id,
        'first_name' => 'Jane',
        'middle_name' => null,
        'last_name' => 'Doe',
    ]);

    $type = ConsultationType::factory()->create(['name' => 'General']);

    DoctorSchedule::factory()->create([
        'user_id' => $doctor->id,
        'consultation_type_id' => $type->id,
        'schedule_type' => 'regular',
        'day_of_week' => 1, // Monday
    ]);

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('setViewMode', 'weekly')
        ->assertSet('viewMode', 'weekly')
        ->assertSee('Jane Doe')
        ->assertSee('General');
});

it('displays exceptions when in exceptions view mode', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create(['first_name' => 'Test', 'last_name' => 'Doctor']);
    $doctor->assignRole('doctor');

    $type = ConsultationType::factory()->create();

    DoctorSchedule::factory()->create([
        'user_id' => $doctor->id,
        'consultation_type_id' => $type->id,
        'schedule_type' => 'exception',
        'date' => now()->addDays(5),
        'is_available' => false,
        'reason' => 'Annual Leave',
    ]);

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('setViewMode', 'exceptions')
        ->assertSet('viewMode', 'exceptions')
        ->assertSee('Annual Leave')
        ->assertSee('Not Available');
});

// ==================== VIEW MODE TESTS ====================

it('can switch between weekly and exceptions view', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->assertSet('viewMode', 'overview')
        ->call('setViewMode', 'weekly')
        ->assertSet('viewMode', 'weekly')
        ->call('setViewMode', 'exceptions')
        ->assertSet('viewMode', 'exceptions')
        ->call('setViewMode', 'weekly')
        ->assertSet('viewMode', 'weekly');
});

// ==================== FILTER TESTS ====================

it('can filter by doctor', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor1 = User::factory()->create(['first_name' => 'First', 'last_name' => 'Doctor']);
    $doctor1->assignRole('doctor');

    $doctor2 = User::factory()->create(['first_name' => 'Second', 'last_name' => 'Doctor']);
    $doctor2->assignRole('doctor');

    $type = ConsultationType::factory()->create();

    DoctorSchedule::factory()->create([
        'user_id' => $doctor1->id,
        'consultation_type_id' => $type->id,
        'schedule_type' => 'regular',
        'day_of_week' => 1,
    ]);

    DoctorSchedule::factory()->create([
        'user_id' => $doctor2->id,
        'consultation_type_id' => $type->id,
        'schedule_type' => 'regular',
        'day_of_week' => 2,
    ]);

    $component = Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('setViewMode', 'weekly')
        ->set('doctorFilter', (string) $doctor1->id);

    $weeklySchedules = $component->get('weeklySchedules');

    expect($weeklySchedules)->toHaveKey($doctor1->id);
    expect($weeklySchedules)->not->toHaveKey($doctor2->id);
});

it('can filter by consultation type', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    $type1 = ConsultationType::factory()->create(['name' => 'Obstetrics']);
    $type2 = ConsultationType::factory()->create(['name' => 'Pediatrics']);

    DoctorSchedule::factory()->create([
        'user_id' => $doctor->id,
        'consultation_type_id' => $type1->id,
        'schedule_type' => 'regular',
        'day_of_week' => 1,
    ]);

    DoctorSchedule::factory()->create([
        'user_id' => $doctor->id,
        'consultation_type_id' => $type2->id,
        'schedule_type' => 'regular',
        'day_of_week' => 2,
    ]);

    $component = Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('setViewMode', 'weekly')
        ->set('consultationTypeFilter', (string) $type1->id);

    $weeklySchedules = $component->get('weeklySchedules');
    $allTypeIds = collect($weeklySchedules)->flatMap(fn ($d) => array_keys($d['schedules']))->values()->all();

    expect($allTypeIds)->toContain($type1->id);
    expect($allTypeIds)->not->toContain($type2->id);
});

// ==================== ADD SCHEDULE MODAL TESTS ====================

it('can open add schedule modal', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->assertSet('showScheduleModal', false)
        ->call('openAddScheduleModal')
        ->assertSet('showScheduleModal', true)
        ->assertSet('editScheduleId', null);
});

it('can close schedule modal', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('openAddScheduleModal')
        ->assertSet('showScheduleModal', true)
        ->call('closeScheduleModal')
        ->assertSet('showScheduleModal', false);
});

it('can save a new schedule', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    $type = ConsultationType::factory()->create();

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('openAddScheduleModal')
        ->set('scheduleDoctor', $doctor->id)
        ->set('scheduleConsultationType', $type->id)
        ->set('scheduleDays', [1, 3, 5]) // Mon, Wed, Fri
        ->set('scheduleStartTime', '09:00')
        ->set('scheduleEndTime', '16:00')
        ->call('saveSchedule')
        ->assertSet('showScheduleModal', false);

    expect(DoctorSchedule::where('user_id', $doctor->id)->count())->toBe(3);
    expect(DoctorSchedule::where('user_id', $doctor->id)->where('day_of_week', 1)->exists())->toBeTrue();
    expect(DoctorSchedule::where('user_id', $doctor->id)->where('day_of_week', 3)->exists())->toBeTrue();
    expect(DoctorSchedule::where('user_id', $doctor->id)->where('day_of_week', 5)->exists())->toBeTrue();
});

it('validates schedule requires at least one day', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    $type = ConsultationType::factory()->create();

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('openAddScheduleModal')
        ->set('scheduleDoctor', $doctor->id)
        ->set('scheduleConsultationType', $type->id)
        ->set('scheduleDays', []) // No days selected
        ->call('saveSchedule')
        ->assertHasErrors(['scheduleDays']);
});

// ==================== EDIT SCHEDULE TESTS ====================

it('can open edit schedule modal with existing data', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    $type = ConsultationType::factory()->create();

    DoctorSchedule::factory()->create([
        'user_id' => $doctor->id,
        'consultation_type_id' => $type->id,
        'schedule_type' => 'regular',
        'day_of_week' => 1,
        'start_time' => '09:00',
        'end_time' => '15:00',
    ]);

    DoctorSchedule::factory()->create([
        'user_id' => $doctor->id,
        'consultation_type_id' => $type->id,
        'schedule_type' => 'regular',
        'day_of_week' => 3,
        'start_time' => '09:00',
        'end_time' => '15:00',
    ]);

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('openEditScheduleModal', $doctor->id, $type->id)
        ->assertSet('showScheduleModal', true)
        ->assertSet('scheduleDoctor', (string) $doctor->id)
        ->assertSet('scheduleConsultationType', (string) $type->id)
        ->assertSet('scheduleDays', [1, 3]);
});

// ==================== DELETE SCHEDULE TESTS ====================

it('can delete a schedule', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    $type = ConsultationType::factory()->create();

    DoctorSchedule::factory()->create([
        'user_id' => $doctor->id,
        'consultation_type_id' => $type->id,
        'schedule_type' => 'regular',
        'day_of_week' => 1,
    ]);

    DoctorSchedule::factory()->create([
        'user_id' => $doctor->id,
        'consultation_type_id' => $type->id,
        'schedule_type' => 'regular',
        'day_of_week' => 2,
    ]);

    expect(DoctorSchedule::where('user_id', $doctor->id)->count())->toBe(2);

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('confirmDeleteSchedule', $doctor->id, $type->id)
        ->assertSet('showDeleteModal', true)
        ->call('deleteConfirmed')
        ->assertSet('showDeleteModal', false);

    expect(DoctorSchedule::where('user_id', $doctor->id)->count())->toBe(0);
});

// ==================== EXCEPTION MODAL TESTS ====================

it('can open add exception modal', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->assertSet('showExceptionModal', false)
        ->call('openAddExceptionModal')
        ->assertSet('showExceptionModal', true)
        ->assertSet('editExceptionId', null);
});

it('can save a new exception (leave)', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    $type = ConsultationType::factory()->create();

    $futureDate = now()->addDays(10)->format('Y-m-d');

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('openAddExceptionModal')
        ->set('exceptionDoctor', $doctor->id)
        ->set('exceptionConsultationType', $type->id)
        ->set('exceptionDate', $futureDate)
        ->set('exceptionIsAvailable', false)
        ->set('exceptionReason', 'Annual Leave')
        ->call('saveException')
        ->assertSet('showExceptionModal', false);

    $exception = DoctorSchedule::where('schedule_type', 'exception')
        ->where('user_id', $doctor->id)
        ->first();

    expect($exception)->not->toBeNull();
    expect($exception->is_available)->toBeFalse();
    expect($exception->reason)->toBe('Annual Leave');
});

it('can save a new exception (extra day)', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    $type = ConsultationType::factory()->create();

    $futureDate = now()->addDays(14)->format('Y-m-d');

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('openAddExceptionModal')
        ->set('exceptionDoctor', $doctor->id)
        ->set('exceptionConsultationType', $type->id)
        ->set('exceptionDate', $futureDate)
        ->set('exceptionIsAvailable', true)
        ->set('exceptionStartTime', '10:00')
        ->set('exceptionEndTime', '14:00')
        ->set('exceptionReason', 'Special Clinic')
        ->call('saveException')
        ->assertSet('showExceptionModal', false);

    $exception = DoctorSchedule::where('schedule_type', 'exception')
        ->where('user_id', $doctor->id)
        ->first();

    expect($exception)->not->toBeNull();
    expect($exception->is_available)->toBeTrue();
    expect($exception->reason)->toBe('Special Clinic');
});

it('prevents duplicate exception for same date', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    $type = ConsultationType::factory()->create();

    $futureDate = now()->addDays(7)->format('Y-m-d');

    // Create existing exception
    DoctorSchedule::factory()->create([
        'user_id' => $doctor->id,
        'consultation_type_id' => $type->id,
        'schedule_type' => 'exception',
        'date' => $futureDate,
        'day_of_week' => null,
    ]);

    // Verify the record exists
    expect(DoctorSchedule::where('user_id', $doctor->id)
        ->where('consultation_type_id', $type->id)
        ->where('schedule_type', 'exception')
        ->whereDate('date', $futureDate)
        ->exists())->toBeTrue();

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('openAddExceptionModal')
        ->set('exceptionDoctor', (string) $doctor->id)
        ->set('exceptionConsultationType', (string) $type->id)
        ->set('exceptionDate', $futureDate)
        ->set('exceptionIsAvailable', false)
        ->set('exceptionReason', 'Duplicate test')
        ->call('saveException')
        ->assertHasErrors(['exceptionDate']);
});

// ==================== EDIT EXCEPTION TESTS ====================

it('can open edit exception modal with existing data', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    $type = ConsultationType::factory()->create();

    $exception = DoctorSchedule::factory()->create([
        'user_id' => $doctor->id,
        'consultation_type_id' => $type->id,
        'schedule_type' => 'exception',
        'date' => now()->addDays(5),
        'is_available' => false,
        'reason' => 'Sick Leave',
    ]);

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('openEditExceptionModal', $exception->id)
        ->assertSet('showExceptionModal', true)
        ->assertSet('editExceptionId', $exception->id)
        ->assertSet('exceptionDoctor', (string) $doctor->id)
        ->assertSet('exceptionIsAvailable', false)
        ->assertSet('exceptionReason', 'Sick Leave');
});

// ==================== DELETE EXCEPTION TESTS ====================

it('can delete an exception', function () {
    $nurse = User::factory()->create();
    $nurse->assignRole('nurse');

    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    $type = ConsultationType::factory()->create();

    $exception = DoctorSchedule::factory()->create([
        'user_id' => $doctor->id,
        'consultation_type_id' => $type->id,
        'schedule_type' => 'exception',
        'date' => now()->addDays(5),
    ]);

    Livewire::actingAs($nurse)
        ->test(DoctorSchedules::class)
        ->call('confirmDeleteException', $exception->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('deleteType', 'exception')
        ->call('deleteConfirmed')
        ->assertSet('showDeleteModal', false);

    expect(DoctorSchedule::find($exception->id))->toBeNull();
});
