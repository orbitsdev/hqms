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
    Role::findOrCreate('doctor', 'web');

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
            ->assertSee('John');
    });

    it('can search queues by patient name', function () {
        $patient1 = User::factory()->create();
        $appointment1 = Appointment::factory()->create([
            'user_id' => $patient1->id,
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Maria',
            'patient_last_name' => 'Santos',
        ]);
        Queue::factory()->today()->create([
            'appointment_id' => $appointment1->id,
            'user_id' => $patient1->id,
            'consultation_type_id' => $this->consultationType->id,
            'queue_number' => 1,
        ]);

        $patient2 = User::factory()->create();
        $appointment2 = Appointment::factory()->create([
            'user_id' => $patient2->id,
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Pedro',
            'patient_last_name' => 'Cruz',
        ]);
        Queue::factory()->today()->create([
            'appointment_id' => $appointment2->id,
            'user_id' => $patient2->id,
            'consultation_type_id' => $this->consultationType->id,
            'queue_number' => 2,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->set('status', 'all')
            ->set('search', 'Maria')
            ->assertSee('Maria')
            ->assertDontSee('Pedro');
    });

    it('can filter queues by consultation type', function () {
        $pedType = ConsultationType::factory()->create([
            'code' => 'ped',
            'name' => 'Pediatrics',
            'short_name' => 'P',
        ]);

        $patient1 = User::factory()->create();
        $appointment1 = Appointment::factory()->create([
            'user_id' => $patient1->id,
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'OB',
            'patient_last_name' => 'Patient',
        ]);
        Queue::factory()->today()->create([
            'appointment_id' => $appointment1->id,
            'user_id' => $patient1->id,
            'consultation_type_id' => $this->consultationType->id,
            'queue_number' => 1,
        ]);

        $patient2 = User::factory()->create();
        $appointment2 = Appointment::factory()->create([
            'user_id' => $patient2->id,
            'consultation_type_id' => $pedType->id,
            'patient_first_name' => 'Pedia',
            'patient_last_name' => 'Patient',
        ]);
        Queue::factory()->today()->create([
            'appointment_id' => $appointment2->id,
            'user_id' => $patient2->id,
            'consultation_type_id' => $pedType->id,
            'queue_number' => 1,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->set('status', 'all')
            ->call('setConsultationType', $this->consultationType->id)
            ->assertSee('OB')
            ->assertDontSee('Pedia');
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
    it('can open stop serving modal', function () {
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
            ->call('openStopServingModal', $queue->id)
            ->assertSet('showStopServingModal', true)
            ->assertSet('stopServingQueueId', $queue->id);
    });

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
            ->call('openStopServingModal', $queue->id)
            ->call('confirmStopServing');

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
            ->call('openStopServingModal', $queue->id)
            ->call('confirmStopServing');

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
            ->call('openStopServingModal', $queue->id)
            ->call('confirmStopServing');

        expect(MedicalRecord::find($medicalRecord->id))->not->toBeNull();
    });

    it('cannot open stop modal for non-serving queue', function () {
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
            ->call('openStopServingModal', $queue->id)
            ->assertSet('showStopServingModal', false);
    });
});

describe('Skip and Requeue', function () {
    it('can open skip modal', function () {
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
            ->call('openSkipModal', $queue->id)
            ->assertSet('showSkipModal', true)
            ->assertSet('skipQueueId', $queue->id);
    });

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
            ->call('openSkipModal', $queue->id)
            ->call('confirmSkip')
            ->assertSet('skipConfirmed', true);

        $queue->refresh();

        expect($queue->status)->toBe('skipped');
    });

    it('can requeue immediately after skipping from modal', function () {
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
            ->call('openSkipModal', $queue->id)
            ->call('confirmSkip')
            ->assertSet('skipConfirmed', true)
            ->call('requeueFromSkipModal')
            ->assertSet('showSkipModal', false);

        $queue->refresh();

        expect($queue->status)->toBe('waiting');
    });

    it('can open requeue modal', function () {
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
            ->call('openRequeueModal', $queue->id)
            ->assertSet('showRequeueModal', true)
            ->assertSet('requeueQueueId', $queue->id);
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
            ->call('openRequeueModal', $queue->id)
            ->call('confirmRequeue');

        $queue->refresh();

        expect($queue->status)->toBe('waiting')
            ->and($queue->called_at)->toBeNull();
    });

    it('keeps the same queue number when requeued', function () {
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        $queue = Queue::factory()->today()->skipped()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'queue_number' => 5,
        ]);

        $originalNumber = $queue->queue_number;

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('openRequeueModal', $queue->id)
            ->call('confirmRequeue');

        $queue->refresh();

        expect($queue->queue_number)->toBe($originalNumber);
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

    it('can forward patient without vitals since vitals are optional', function () {
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

        expect($queue->status)->toBe('completed');
    });
});

describe('DOB Fallback in Start Serving', function () {
    it('uses appointment DOB when available', function () {
        $patient = User::factory()->create();
        PersonalInformation::factory()->create([
            'user_id' => $patient->id,
            'date_of_birth' => '1990-01-01',
        ]);

        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Test',
            'patient_last_name' => 'Patient',
            'patient_date_of_birth' => '1995-06-15',
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

        $record = MedicalRecord::where('queue_id', $queue->id)->first();

        expect($record)->not->toBeNull()
            ->and($record->patient_date_of_birth->format('Y-m-d'))->toBe('1995-06-15');
    });

    it('falls back to user personal information DOB when no appointment exists', function () {
        $patient = User::factory()->create();
        PersonalInformation::factory()->create([
            'user_id' => $patient->id,
            'date_of_birth' => '1990-01-01',
        ]);

        $queue = Queue::factory()->today()->waiting()->create([
            'appointment_id' => null,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('startServing', $queue->id);

        $record = MedicalRecord::where('queue_id', $queue->id)->first();

        expect($record)->not->toBeNull()
            ->and($record->patient_date_of_birth->format('Y-m-d'))->toBe('1990-01-01');
    });
});

describe('Save Interview Validation', function () {
    it('normalizes locale-formatted date of birth before validation', function () {
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

        MedicalRecord::factory()->create([
            'queue_id' => $queue->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        // DD/MM/YYYY format from browser locale should be normalized to Y-m-d
        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('openInterviewModal', $queue->id)
            ->set('patientDateOfBirth', '13/01/2001')
            ->call('saveInterview')
            ->assertHasNoErrors('patientDateOfBirth');

        $record = MedicalRecord::where('queue_id', $queue->id)->first();
        expect($record->patient_date_of_birth->format('Y-m-d'))->toBe('2001-01-13');
    });

    it('converts empty date of birth string to null before validation', function () {
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

        MedicalRecord::factory()->create([
            'queue_id' => $queue->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('openInterviewModal', $queue->id)
            ->set('patientDateOfBirth', '')
            ->call('saveInterview')
            ->assertHasNoErrors('patientDateOfBirth');
    });

    it('converts empty last menstrual period string to null before validation', function () {
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

        MedicalRecord::factory()->create([
            'queue_id' => $queue->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('openInterviewModal', $queue->id)
            ->set('lastMenstrualPeriod', '')
            ->call('saveInterview')
            ->assertHasNoErrors('lastMenstrualPeriod');
    });

    it('saves interview even with unparseable date of birth by nullifying it', function () {
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

        MedicalRecord::factory()->create([
            'queue_id' => $queue->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('openInterviewModal', $queue->id)
            ->set('patientDateOfBirth', 'not-a-date')
            ->set('patientZipCode', '4000')
            ->call('saveInterview')
            ->assertHasNoErrors();

        $record = MedicalRecord::where('queue_id', $queue->id)->first();
        expect($record->patient_date_of_birth)->toBeNull()
            ->and($record->patient_zip_code)->toBe('4000');
    });

    it('persists non-date fields even when DOB is empty', function () {
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

        MedicalRecord::factory()->create([
            'queue_id' => $queue->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('openInterviewModal', $queue->id)
            ->set('patientDateOfBirth', '')
            ->set('patientZipCode', '1234')
            ->set('emergencyContactName', 'Jane Doe')
            ->set('emergencyContactNumber', '09171234567')
            ->set('emergencyContactRelationship', 'Mother')
            ->call('saveInterview')
            ->assertHasNoErrors();

        $record = MedicalRecord::where('queue_id', $queue->id)->first();
        expect($record->patient_zip_code)->toBe('1234')
            ->and($record->emergency_contact_name)->toBe('Jane Doe')
            ->and($record->emergency_contact_number)->toBe('09171234567')
            ->and($record->emergency_contact_relationship)->toBe('Mother');
    });

    it('allows saving interview with nullable date of birth', function () {
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

        MedicalRecord::factory()->create([
            'queue_id' => $queue->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('openInterviewModal', $queue->id)
            ->set('patientDateOfBirth', null)
            ->call('saveInterview')
            ->assertHasNoErrors('patientDateOfBirth');
    });

    it('round-trip: saved interview data loads back when modal is reopened', function () {
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

        MedicalRecord::factory()->create([
            'queue_id' => $queue->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        // Save interview with specific values
        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('openInterviewModal', $queue->id)
            ->set('patientDateOfBirth', '2000-06-25')
            ->set('patientZipCode', '9805')
            ->set('emergencyContactName', 'Alyssa Butler')
            ->set('emergencyContactNumber', '09171234567')
            ->set('emergencyContactRelationship', 'Sibling')
            ->call('saveInterview')
            ->assertHasNoErrors()
            // After save, modal is closed — now reopen and verify data loaded back
            ->call('openInterviewModal', $queue->id)
            ->assertSet('patientDateOfBirth', '2000-06-25')
            ->assertSet('patientZipCode', '9805')
            ->assertSet('emergencyContactName', 'Alyssa Butler')
            ->assertSet('emergencyContactNumber', '09171234567')
            ->assertSet('emergencyContactRelationship', 'Sibling');
    });

    it('round-trip with step navigation: data persists through Next steps and reopen', function () {
        $patient = User::factory()->create();
        PersonalInformation::factory()->create([
            'user_id' => $patient->id,
            'date_of_birth' => '2000-01-01',
        ]);
        $appointment = Appointment::factory()->create([
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
            'patient_first_name' => 'Cyrus Jake',
            'patient_last_name' => 'Samuray',
            'patient_date_of_birth' => '2000-01-01',
            'status' => 'checked_in',
        ]);

        $queue = Queue::factory()->today()->waiting()->create([
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        // Start serving creates the medical record
        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('startServing', $queue->id);

        // Open interview, navigate through steps (simulating real user flow)
        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('openInterviewModal', $queue->id)
            // Step 1: Patient — edit DOB
            ->set('patientDateOfBirth', '2022-06-25')
            ->call('nextInterviewStep')
            // Step 2: Address — add zip code
            ->set('patientZipCode', '9805')
            ->call('nextInterviewStep')
            // Step 3: Companion — add emergency contact
            ->set('emergencyContactName', 'Alyssa Butler')
            ->set('emergencyContactNumber', '09171234567')
            ->set('emergencyContactRelationship', 'Sibling')
            ->call('nextInterviewStep')
            // Step 4: Medical — skip
            ->call('nextInterviewStep')
            // Step 5: Vitals — save
            ->call('saveInterview')
            ->assertHasNoErrors();

        // Verify DB has the data
        $record = MedicalRecord::where('queue_id', $queue->id)->first();
        expect($record->patient_date_of_birth->format('Y-m-d'))->toBe('2022-06-25')
            ->and($record->patient_zip_code)->toBe('9805')
            ->and($record->emergency_contact_name)->toBe('Alyssa Butler')
            ->and($record->emergency_contact_number)->toBe('09171234567')
            ->and($record->emergency_contact_relationship)->toBe('Sibling');

        // Reopen and verify loaded back
        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('openInterviewModal', $queue->id)
            ->assertSet('patientDateOfBirth', '2022-06-25')
            ->assertSet('patientZipCode', '9805')
            ->assertSet('emergencyContactName', 'Alyssa Butler')
            ->assertSet('emergencyContactNumber', '09171234567')
            ->assertSet('emergencyContactRelationship', 'Sibling');
    });

    it('allows saving interview without vital signs', function () {
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

        MedicalRecord::factory()->create([
            'queue_id' => $queue->id,
            'user_id' => $patient->id,
            'consultation_type_id' => $this->consultationType->id,
        ]);

        Livewire::actingAs($this->nurse)
            ->test(TodayQueue::class)
            ->call('openInterviewModal', $queue->id)
            ->set('temperature', null)
            ->set('bloodPressure', null)
            ->set('cardiacRate', null)
            ->set('respiratoryRate', null)
            ->call('saveInterview')
            ->assertHasNoErrors();
    });
});
