<?php

use App\Livewire\Doctor\PatientQueue;
use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\PersonalInformation;
use App\Models\Queue;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::findOrCreate('doctor', 'web');
    Role::findOrCreate('nurse', 'web');
    Role::findOrCreate('patient', 'web');

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

it('renders the queue page for doctors', function () {
    actingAs($this->doctor)
        ->get(route('doctor.queue'))
        ->assertSuccessful()
        ->assertSee('Patient Queue');
});

it('denies access to non-doctors', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('doctor.queue'))
        ->assertForbidden();
});

// ==================== DISPLAY TESTS ====================

it('displays patients waiting for examination', function () {
    $patient = User::factory()->create();
    $patient->assignRole('patient');

    $appointment = Appointment::factory()->create([
        'user_id' => $patient->id,
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'John',
        'patient_last_name' => 'Doe',
    ]);

    $queue = Queue::factory()->today()->create([
        'appointment_id' => $appointment->id,
        'user_id' => $patient->id,
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'completed', // Forwarded by nurse
        'serving_ended_at' => now(),
    ]);

    MedicalRecord::factory()->create([
        'queue_id' => $queue->id,
        'user_id' => $patient->id,
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'John',
        'patient_last_name' => 'Doe',
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
        'examined_at' => null, // Not yet examined
    ]);

    Livewire::actingAs($this->doctor)
        ->test(PatientQueue::class)
        ->assertSee('John')
        ->assertSee('Doe');
});

it('only shows patients with matching consultation types', function () {
    $unassignedType = ConsultationType::factory()->create([
        'code' => 'ob',
        'name' => 'OB-GYN',
        'short_name' => 'O',
    ]);

    // Patient with matching consultation type
    $patient1 = User::factory()->create();
    $appointment1 = Appointment::factory()->create([
        'user_id' => $patient1->id,
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Assigned',
        'patient_last_name' => 'Patient',
    ]);
    $queue1 = Queue::factory()->today()->create([
        'appointment_id' => $appointment1->id,
        'user_id' => $patient1->id,
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'completed',
        'serving_ended_at' => now(),
    ]);
    MedicalRecord::factory()->create([
        'queue_id' => $queue1->id,
        'user_id' => $patient1->id,
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Assigned',
        'patient_last_name' => 'Patient',
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
        'examined_at' => null,
    ]);

    // Patient with unassigned consultation type
    $patient2 = User::factory()->create();
    $appointment2 = Appointment::factory()->create([
        'user_id' => $patient2->id,
        'consultation_type_id' => $unassignedType->id,
        'patient_first_name' => 'Unassigned',
        'patient_last_name' => 'Patient',
    ]);
    $queue2 = Queue::factory()->today()->create([
        'appointment_id' => $appointment2->id,
        'user_id' => $patient2->id,
        'consultation_type_id' => $unassignedType->id,
        'status' => 'completed',
        'serving_ended_at' => now(),
    ]);
    MedicalRecord::factory()->create([
        'queue_id' => $queue2->id,
        'user_id' => $patient2->id,
        'consultation_type_id' => $unassignedType->id,
        'patient_first_name' => 'Unassigned',
        'patient_last_name' => 'Patient',
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
        'examined_at' => null,
    ]);

    Livewire::actingAs($this->doctor)
        ->test(PatientQueue::class)
        ->assertSee('Assigned')
        ->assertDontSee('Unassigned');
});

it('can start examination for a patient', function () {
    $patient = User::factory()->create();
    $patient->assignRole('patient');

    $appointment = Appointment::factory()->create([
        'user_id' => $patient->id,
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Test',
        'patient_last_name' => 'Patient',
    ]);

    $queue = Queue::factory()->today()->create([
        'appointment_id' => $appointment->id,
        'user_id' => $patient->id,
        'consultation_type_id' => $this->consultationType->id,
        'status' => 'completed',
        'serving_ended_at' => now(),
    ]);

    $record = MedicalRecord::factory()->create([
        'queue_id' => $queue->id,
        'user_id' => $patient->id,
        'consultation_type_id' => $this->consultationType->id,
        'patient_first_name' => 'Test',
        'patient_last_name' => 'Patient',
        'status' => 'in_progress',
        'vital_signs_recorded_at' => now(),
        'examined_at' => null,
    ]);

    Livewire::actingAs($this->doctor)
        ->test(PatientQueue::class)
        ->call('startExamination', $queue->id)
        ->assertRedirect(route('doctor.examine', $record));
});
