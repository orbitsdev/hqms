<?php

use App\Livewire\Nurse\Appointments;
use App\Livewire\Nurse\AppointmentShow;
use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\PersonalInformation;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

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
        'code' => 'ob',
        'name' => 'Obstetrics',
        'short_name' => 'O',
    ]);
});

describe('Nurse Appointments List', function () {
    it('renders the appointments list page', function () {
        actingAs($this->nurse)
            ->get(route('nurse.appointments'))
            ->assertSuccessful()
            ->assertSee('Appointments');
    });

    it('shows confirmed appointments', function () {
        $appointment = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'confirmed',
            'patient_first_name' => 'John',
            'patient_last_name' => 'Doe',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(Appointments::class)
            ->assertSee('John')
            ->assertSee('Doe')
            ->assertSee('Confirmed');
    });

    it('filters appointments by status', function () {
        $confirmedAppointment = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'confirmed',
            'patient_first_name' => 'Confirmed',
            'patient_last_name' => 'Patient',
        ]);

        $approvedAppointment = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'approved',
            'patient_first_name' => 'Approved',
            'patient_last_name' => 'Patient',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(Appointments::class)
            ->assertSee('Confirmed Patient')
            ->assertDontSee('Approved Patient')
            ->call('setStatus', 'approved')
            ->assertDontSee('Confirmed Patient')
            ->assertSee('Approved Patient');
    });

    it('searches appointments by patient name', function () {
        $appointment1 = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Maria',
            'patient_last_name' => 'Santos',
        ]);

        $appointment2 = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Juan',
            'patient_last_name' => 'Cruz',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(Appointments::class)
            ->call('setStatus', 'all')
            ->set('search', 'Maria')
            ->assertSee('Maria Santos')
            ->assertDontSee('Juan Cruz');
    });

    it('filters appointments by consultation type', function () {
        $obType = $this->consultationType;
        $pedType = ConsultationType::factory()->create([
            'code' => 'pedia',
            'name' => 'Pediatrics',
            'short_name' => 'P',
        ]);

        $obAppointment = Appointment::factory()->create([
            'consultation_type_id' => $obType->id,
            'patient_first_name' => 'OB',
            'patient_last_name' => 'Patient',
        ]);

        $pedAppointment = Appointment::factory()->create([
            'consultation_type_id' => $pedType->id,
            'patient_first_name' => 'Pedia',
            'patient_last_name' => 'Patient',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(Appointments::class)
            ->call('setStatus', 'all')
            ->set('consultationTypeFilter', $obType->id)
            ->assertSee('OB Patient')
            ->assertDontSee('Pedia Patient');
    });

    it('clears all filters', function () {
        Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Test',
            'patient_last_name' => 'Patient',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(Appointments::class)
            ->set('search', 'nonexistent')
            ->assertDontSee('Test Patient')
            ->call('clearFilters')
            ->assertSee('Test Patient');
    });
});

describe('Nurse Appointment Show', function () {
    it('renders the appointment details page', function () {
        $appointment = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
        ]);

        actingAs($this->nurse)
            ->get(route('nurse.appointments.show', $appointment))
            ->assertSuccessful()
            ->assertSee('Appointment Details');
    });

    it('displays patient information', function () {
        $appointment = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Maria',
            'patient_last_name' => 'Santos',
            'patient_gender' => 'female',
            'chief_complaints' => 'Test complaints here',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(AppointmentShow::class, ['appointment' => $appointment])
            ->assertSee('Maria')
            ->assertSee('Santos')
            ->assertSee('Female')
            ->assertSee('Test complaints here');
    });

    it('displays visit type on appointment details', function (string $visitType) {
        $appointment = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'visit_type' => $visitType,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(AppointmentShow::class, ['appointment' => $appointment])
            ->assertSee('Visit Type')
            ->assertSee(ucfirst($visitType));
    })->with(['new', 'old', 'revisit']);
});

describe('Cancel Appointment', function () {
    it('can open the cancel modal', function () {
        $appointment = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'confirmed',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(AppointmentShow::class, ['appointment' => $appointment])
            ->call('openCancelModal')
            ->assertSet('showCancelModal', true);
    });

    it('can cancel a confirmed appointment with reason', function () {
        Notification::fake();

        $patient = User::factory()->create();
        PersonalInformation::factory()->create(['user_id' => $patient->id]);

        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'confirmed',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(AppointmentShow::class, ['appointment' => $appointment])
            ->call('openCancelModal')
            ->set('cancelReason', 'Doctor is not available on this date.')
            ->call('cancelAppointment');

        $appointment->refresh();

        expect($appointment->status)->toBe('cancelled')
            ->and($appointment->cancellation_reason)->toBe('Doctor is not available on this date.');
    });

    it('requires a reason to cancel', function () {
        $appointment = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'confirmed',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(AppointmentShow::class, ['appointment' => $appointment])
            ->call('openCancelModal')
            ->set('cancelReason', '')
            ->call('cancelAppointment')
            ->assertHasErrors(['cancelReason' => 'required']);
    });

    it('requires minimum characters for cancellation reason', function () {
        $appointment = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'confirmed',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(AppointmentShow::class, ['appointment' => $appointment])
            ->call('openCancelModal')
            ->set('cancelReason', 'Too short')
            ->call('cancelAppointment')
            ->assertHasErrors(['cancelReason' => 'min']);
    });

    it('also cancels the queue when cancelling an approved appointment', function () {
        Notification::fake();

        $patient = User::factory()->create();
        PersonalInformation::factory()->create(['user_id' => $patient->id]);

        $appointment = Appointment::factory()->approved()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        $queue = Queue::factory()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'waiting',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(AppointmentShow::class, ['appointment' => $appointment])
            ->call('openCancelModal')
            ->set('cancelReason', 'Patient requested cancellation.')
            ->call('cancelAppointment');

        $queue->refresh();

        expect($queue->status)->toBe('cancelled');
    });
});
