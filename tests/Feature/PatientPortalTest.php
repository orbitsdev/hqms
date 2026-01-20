<?php

use App\Livewire\Patient\ActiveQueue;
use App\Livewire\Patient\AppointmentShow;
use App\Livewire\Patient\Appointments;
use App\Livewire\Patient\BookAppointment;
use App\Livewire\Patient\Dashboard;
use App\Livewire\Patient\MedicalRecordShow;
use App\Livewire\Patient\MedicalRecords;
use App\Livewire\Patient\Profile;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\User;

it('renders patient portal pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/patient')
        ->assertOk()
        ->assertSeeLivewire(Dashboard::class);

    $this->actingAs($user)
        ->get('/patient/profile')
        ->assertOk()
        ->assertSeeLivewire(Profile::class);

    $this->actingAs($user)
        ->get('/patient/appointments')
        ->assertOk()
        ->assertSeeLivewire(Appointments::class);

    $this->actingAs($user)
        ->get('/patient/appointments/book')
        ->assertOk()
        ->assertSeeLivewire(BookAppointment::class);

    $this->actingAs($user)
        ->get('/patient/queue')
        ->assertOk()
        ->assertSeeLivewire(ActiveQueue::class);

    $this->actingAs($user)
        ->get('/patient/records')
        ->assertOk()
        ->assertSeeLivewire(MedicalRecords::class);
});

it('renders appointment and record detail pages', function () {
    $user = User::factory()->create();
    $appointment = Appointment::factory()->create(['user_id' => $user->id]);
    $record = MedicalRecord::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('patient.appointments.show', $appointment))
        ->assertOk()
        ->assertSeeLivewire(AppointmentShow::class);

    $this->actingAs($user)
        ->get(route('patient.records.show', $record))
        ->assertOk()
        ->assertSeeLivewire(MedicalRecordShow::class);
});
