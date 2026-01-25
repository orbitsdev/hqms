<?php

use App\Livewire\Nurse\TodayQueue;
use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\MedicalRecord;
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

describe('Today Queue Page', function () {
    it('renders the today queue page', function () {
        actingAs($this->nurse)
            ->get(route('nurse.queue'))
            ->assertSuccessful()
            ->assertSee("Today's Queue");
    });

    it('displays queues for today', function () {
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'John',
            'patient_last_name' => 'Doe',
        ]);

        Queue::factory()->today()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'queue_number' => 1,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->assertSee('John')
            ->assertSee('Doe');
    });
});

describe('Start Serving', function () {
    it('can start serving a waiting patient', function () {
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'checked_in',
        ]);

        $queue = Queue::factory()->today()->waiting()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('startServing', $queue->id);

        $queue->refresh();

        expect($queue->status)->toBe('serving')
            ->and($queue->served_by)->toBe($this->nurse->id)
            ->and($queue->serving_started_at)->not->toBeNull();
    });

    it('creates medical record when starting to serve', function () {
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Test',
            'patient_last_name' => 'Patient',
        ]);

        $queue = Queue::factory()->today()->waiting()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        expect(MedicalRecord::where('queue_id', $queue->id)->exists())->toBeFalse();

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('startServing', $queue->id);

        expect(MedicalRecord::where('queue_id', $queue->id)->exists())->toBeTrue();
    });
});

describe('Stop Serving', function () {
    it('can stop serving a patient and return to waiting', function () {
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'in_progress',
        ]);

        $queue = Queue::factory()->today()->serving()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'served_by' => $this->nurse->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('stopServing', $queue->id);

        $queue->refresh();
        $appointment->refresh();

        expect($queue->status)->toBe('waiting')
            ->and($queue->served_by)->toBeNull()
            ->and($queue->serving_started_at)->toBeNull()
            ->and($appointment->status)->toBe('checked_in');
    });

    it('deletes medical record without vitals when stopping', function () {
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        $queue = Queue::factory()->today()->serving()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'served_by' => $this->nurse->id,
        ]);

        $medicalRecord = MedicalRecord::factory()->create([
            'queue_id' => $queue->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'vital_signs_recorded_at' => null,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('stopServing', $queue->id);

        expect(MedicalRecord::find($medicalRecord->id))->toBeNull();
    });

    it('preserves medical record with vitals when stopping', function () {
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        $queue = Queue::factory()->today()->serving()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'served_by' => $this->nurse->id,
        ]);

        $medicalRecord = MedicalRecord::factory()->create([
            'queue_id' => $queue->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'vital_signs_recorded_at' => now(),
            'temperature' => '36.5',
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('stopServing', $queue->id);

        expect(MedicalRecord::find($medicalRecord->id))->not->toBeNull();
    });

    it('cannot stop serving a non-serving queue', function () {
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        $queue = Queue::factory()->today()->waiting()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('stopServing', $queue->id);

        $queue->refresh();

        expect($queue->status)->toBe('waiting');
    });
});

describe('Skip and Requeue', function () {
    it('can skip a waiting patient', function () {
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        $queue = Queue::factory()->today()->waiting()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('skipPatient', $queue->id);

        $queue->refresh();

        expect($queue->status)->toBe('skipped');
    });

    it('can requeue a skipped patient', function () {
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        $queue = Queue::factory()->today()->skipped()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('requeuePatient', $queue->id);

        $queue->refresh();

        expect($queue->status)->toBe('waiting')
            ->and($queue->called_at)->toBeNull();
    });
});

describe('Call Patient', function () {
    it('can call a waiting patient', function () {
        Notification::fake();

        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        $queue = Queue::factory()->today()->waiting()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('callPatient', $queue->id);

        $queue->refresh();

        expect($queue->status)->toBe('called')
            ->and($queue->called_at)->not->toBeNull();
    });
});

describe('Forward to Doctor', function () {
    it('can forward patient with vitals to doctor', function () {
        Notification::fake();
        Role::findOrCreate('doctor', 'web');

        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        $queue = Queue::factory()->today()->serving()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'served_by' => $this->nurse->id,
        ]);

        MedicalRecord::factory()->create([
            'queue_id' => $queue->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'vital_signs_recorded_at' => now(),
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('forwardToDoctor', $queue->id);

        $queue->refresh();

        expect($queue->status)->toBe('completed')
            ->and($queue->serving_ended_at)->not->toBeNull();
    });

    it('cannot forward patient without vitals', function () {
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        $queue = Queue::factory()->today()->serving()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'served_by' => $this->nurse->id,
        ]);

        MedicalRecord::factory()->create([
            'queue_id' => $queue->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'vital_signs_recorded_at' => null,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('forwardToDoctor', $queue->id);

        $queue->refresh();

        expect($queue->status)->toBe('serving');
    });
});
