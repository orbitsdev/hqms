<?php

use App\Livewire\Doctor\MySchedule;
use App\Models\ConsultationType;
use App\Models\DoctorSchedule;
use App\Models\PersonalInformation;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::findOrCreate('doctor', 'web');
    Role::findOrCreate('nurse', 'web');

    $this->doctor = User::factory()->create();
    $this->doctor->assignRole('doctor');

    PersonalInformation::factory()->create([
        'user_id' => $this->doctor->id,
        'first_name' => 'Test',
        'last_name' => 'Doctor',
    ]);

    $this->consultationType = ConsultationType::factory()->create([
        'code' => 'ped',
        'name' => 'Pediatrics',
        'short_name' => 'P',
    ]);

    $this->doctor->consultationTypes()->attach($this->consultationType);
});

// ==================== ACCESS TESTS ====================

it('renders the schedule page for doctors', function () {
    actingAs($this->doctor)
        ->get(route('doctor.schedule'))
        ->assertSuccessful()
        ->assertSee('My Schedule');
});

it('denies access to non-doctors', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('doctor.schedule'))
        ->assertForbidden();
});

// ==================== DISPLAY TESTS ====================

it('displays doctors own schedules', function () {
    DoctorSchedule::factory()->create([
        'user_id' => $this->doctor->id,
        'consultation_type_id' => $this->consultationType->id,
        'schedule_type' => 'regular',
        'day_of_week' => 1, // Monday
        'start_time' => '09:00',
        'end_time' => '17:00',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->assertSee('Pediatrics');
});

it('displays exceptions in overview', function () {
    DoctorSchedule::factory()->create([
        'user_id' => $this->doctor->id,
        'consultation_type_id' => $this->consultationType->id,
        'schedule_type' => 'exception',
        'date' => now()->addDays(5),
        'is_available' => false,
        'reason' => 'Annual Leave',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->assertSee('Annual Leave');
});

// ==================== VIEW MODE NAVIGATION TESTS ====================

it('can switch between view modes', function () {
    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->assertSet('viewMode', 'overview')
        ->call('setViewMode', 'weekly')
        ->assertSet('viewMode', 'weekly')
        ->call('setViewMode', 'appointments')
        ->assertSet('viewMode', 'appointments')
        ->call('setViewMode', 'overview')
        ->assertSet('viewMode', 'overview');
});

// ==================== SCHEDULE MANAGEMENT TESTS ====================

it('can open add schedule modal', function () {
    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->assertSet('showScheduleModal', false)
        ->call('openAddScheduleModal')
        ->assertSet('showScheduleModal', true)
        ->assertSet('editScheduleTypeId', null);
});

it('can close schedule modal', function () {
    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->call('openAddScheduleModal')
        ->assertSet('showScheduleModal', true)
        ->call('closeScheduleModal')
        ->assertSet('showScheduleModal', false);
});

it('can save a new schedule', function () {
    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->call('openAddScheduleModal')
        ->set('scheduleConsultationType', $this->consultationType->id)
        ->set('scheduleDays', [1, 3, 5]) // Mon, Wed, Fri
        ->set('scheduleStartTime', '09:00')
        ->set('scheduleEndTime', '16:00')
        ->call('saveSchedule')
        ->assertSet('showScheduleModal', false);

    expect(DoctorSchedule::where('user_id', $this->doctor->id)->count())->toBe(3);
    expect(DoctorSchedule::where('user_id', $this->doctor->id)->where('day_of_week', 1)->exists())->toBeTrue();
    expect(DoctorSchedule::where('user_id', $this->doctor->id)->where('day_of_week', 3)->exists())->toBeTrue();
    expect(DoctorSchedule::where('user_id', $this->doctor->id)->where('day_of_week', 5)->exists())->toBeTrue();
});

it('validates schedule requires at least one day', function () {
    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->call('openAddScheduleModal')
        ->set('scheduleConsultationType', $this->consultationType->id)
        ->set('scheduleDays', []) // No days selected
        ->call('saveSchedule')
        ->assertHasErrors(['scheduleDays']);
});

it('can edit an existing schedule', function () {
    DoctorSchedule::factory()->create([
        'user_id' => $this->doctor->id,
        'consultation_type_id' => $this->consultationType->id,
        'schedule_type' => 'regular',
        'day_of_week' => 1,
        'start_time' => '09:00',
        'end_time' => '15:00',
    ]);

    DoctorSchedule::factory()->create([
        'user_id' => $this->doctor->id,
        'consultation_type_id' => $this->consultationType->id,
        'schedule_type' => 'regular',
        'day_of_week' => 3,
        'start_time' => '09:00',
        'end_time' => '15:00',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->call('openEditScheduleModal', $this->consultationType->id)
        ->assertSet('showScheduleModal', true)
        ->assertSet('scheduleConsultationType', (string) $this->consultationType->id)
        ->assertSet('scheduleDays', [1, 3]);
});

// ==================== SCHEDULE DELETE TESTS ====================

it('can delete a schedule group', function () {
    DoctorSchedule::factory()->create([
        'user_id' => $this->doctor->id,
        'consultation_type_id' => $this->consultationType->id,
        'schedule_type' => 'regular',
        'day_of_week' => 1,
    ]);

    DoctorSchedule::factory()->create([
        'user_id' => $this->doctor->id,
        'consultation_type_id' => $this->consultationType->id,
        'schedule_type' => 'regular',
        'day_of_week' => 2,
    ]);

    expect(DoctorSchedule::where('user_id', $this->doctor->id)->count())->toBe(2);

    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->call('confirmDeleteSchedule', $this->consultationType->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('deleteType', "schedule:{$this->consultationType->id}")
        ->call('deleteConfirmed')
        ->assertSet('showDeleteModal', false);

    expect(DoctorSchedule::where('user_id', $this->doctor->id)->count())->toBe(0);
});

// ==================== EXCEPTION MANAGEMENT TESTS ====================

it('can open add exception modal', function () {
    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->assertSet('showExceptionModal', false)
        ->call('openAddExceptionModal')
        ->assertSet('showExceptionModal', true)
        ->assertSet('editExceptionId', null);
});

it('can save a new exception (leave)', function () {
    $futureDate = now()->addDays(10)->format('Y-m-d');

    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->call('openAddExceptionModal')
        ->set('exceptionConsultationType', $this->consultationType->id)
        ->set('exceptionDate', $futureDate)
        ->set('exceptionIsAvailable', false)
        ->set('exceptionReason', 'Medical Leave')
        ->call('saveException')
        ->assertSet('showExceptionModal', false);

    $exception = DoctorSchedule::where('schedule_type', 'exception')
        ->where('user_id', $this->doctor->id)
        ->first();

    expect($exception)->not->toBeNull();
    expect($exception->is_available)->toBeFalse();
    expect($exception->reason)->toBe('Medical Leave');
});

it('can save a new exception (extra clinic day)', function () {
    $futureDate = now()->addDays(14)->format('Y-m-d');

    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->call('openAddExceptionModal')
        ->set('exceptionConsultationType', $this->consultationType->id)
        ->set('exceptionDate', $futureDate)
        ->set('exceptionIsAvailable', true)
        ->set('exceptionStartTime', '08:00')
        ->set('exceptionEndTime', '12:00')
        ->set('exceptionReason', 'Saturday Special Clinic')
        ->call('saveException')
        ->assertSet('showExceptionModal', false);

    $exception = DoctorSchedule::where('schedule_type', 'exception')
        ->where('user_id', $this->doctor->id)
        ->first();

    expect($exception)->not->toBeNull();
    expect($exception->is_available)->toBeTrue();
    // Check that start_time is set (format may vary based on DB driver)
    expect($exception->start_time)->not->toBeNull();
    expect($exception->end_time)->not->toBeNull();
});

it('requires valid consultation type for exception', function () {
    $futureDate = now()->addDays(7)->format('Y-m-d');

    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->call('openAddExceptionModal')
        ->set('exceptionConsultationType', '') // Empty consultation type
        ->set('exceptionDate', $futureDate)
        ->set('exceptionIsAvailable', false)
        ->set('exceptionReason', 'Leave')
        ->call('saveException')
        ->assertHasErrors(['exceptionConsultationType']);
});

it('can edit an exception', function () {
    $exception = DoctorSchedule::factory()->create([
        'user_id' => $this->doctor->id,
        'consultation_type_id' => $this->consultationType->id,
        'schedule_type' => 'exception',
        'date' => now()->addDays(5),
        'is_available' => false,
        'reason' => 'Original Reason',
    ]);

    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->call('openEditExceptionModal', $exception->id)
        ->assertSet('showExceptionModal', true)
        ->assertSet('editExceptionId', $exception->id)
        ->assertSet('exceptionReason', 'Original Reason')
        ->set('exceptionReason', 'Updated Reason')
        ->call('saveException');

    $exception->refresh();
    expect($exception->reason)->toBe('Updated Reason');
});

it('can delete an exception', function () {
    $exception = DoctorSchedule::factory()->create([
        'user_id' => $this->doctor->id,
        'consultation_type_id' => $this->consultationType->id,
        'schedule_type' => 'exception',
        'date' => now()->addDays(5),
    ]);

    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->call('confirmDeleteException', $exception->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('deleteType', "exception:{$exception->id}")
        ->call('deleteConfirmed')
        ->assertSet('showDeleteModal', false);

    expect(DoctorSchedule::find($exception->id))->toBeNull();
});

// ==================== WEEK NAVIGATION TESTS ====================

it('can navigate weeks in calendar', function () {
    $initialWeekStart = now()->startOfWeek()->format('Y-m-d');

    Livewire::actingAs($this->doctor)
        ->test(MySchedule::class)
        ->assertSet('weekStart', $initialWeekStart)
        ->call('nextWeek')
        ->call('previousWeek')
        ->assertSet('weekStart', $initialWeekStart)
        ->call('goToToday')
        ->assertSet('weekStart', $initialWeekStart);
});
