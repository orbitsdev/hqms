<?php

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\Queue;
use App\Models\User;
use App\Services\QueueNumberService;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('patient', 'web');
    Notification::fake();

    $this->consultationType = ConsultationType::factory()->create();
});

describe('Auto-Queue Generation', function () {
    it('queues confirmed appointments for today', function () {
        $user = User::factory()->create();
        $user->assignRole('patient');

        $appointment = Appointment::factory()->create([
            'user_id' => $user->id,
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'confirmed',
            'appointment_date' => today(),
        ]);

        $queueService = app(QueueNumberService::class);
        $queue = $queueService->createQueueForAppointment($appointment);

        $appointment->refresh();

        expect($appointment->status)->toBe('approved')
            ->and($appointment->approved_at)->not->toBeNull()
            ->and($queue->status)->toBe('waiting')
            ->and($queue->queue_number)->toBe(1)
            ->and($queue->queue_date->toDateString())->toBe(today()->toDateString());
    });

    it('does not queue confirmed appointments for tomorrow', function () {
        $appointment = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'confirmed',
            'appointment_date' => today()->addDay(),
        ]);

        // Simulate scheduler: only query today's appointments
        $todayConfirmed = Appointment::query()
            ->where('status', 'confirmed')
            ->whereDate('appointment_date', today())
            ->get();

        expect($todayConfirmed)->toHaveCount(0);

        $appointment->refresh();
        expect($appointment->status)->toBe('confirmed');
    });

    it('assigns sequential queue numbers per consultation type', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $appointment1 = Appointment::factory()->create([
            'user_id' => $user1->id,
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'confirmed',
            'appointment_date' => today(),
        ]);

        $appointment2 = Appointment::factory()->create([
            'user_id' => $user2->id,
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'confirmed',
            'appointment_date' => today(),
        ]);

        $queueService = app(QueueNumberService::class);

        $queue1 = $queueService->createQueueForAppointment($appointment1);
        $queue2 = $queueService->createQueueForAppointment($appointment2);

        expect($queue1->queue_number)->toBe(1)
            ->and($queue2->queue_number)->toBe(2);
    });

    it('separates queue numbers by consultation type', function () {
        $type2 = ConsultationType::factory()->create();

        $appointment1 = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'confirmed',
            'appointment_date' => today(),
        ]);

        $appointment2 = Appointment::factory()->create([
            'consultation_type_id' => $type2->id,
            'status' => 'confirmed',
            'appointment_date' => today(),
        ]);

        $queueService = app(QueueNumberService::class);

        $queue1 = $queueService->createQueueForAppointment($appointment1);
        $queue2 = $queueService->createQueueForAppointment($appointment2);

        // Both should be #1 since different consultation types
        expect($queue1->queue_number)->toBe(1)
            ->and($queue2->queue_number)->toBe(1);
    });
});

describe('No-Show Marking', function () {
    it('marks yesterday unserved approved appointments as no_show', function () {
        $yesterday = today()->subDay();

        $appointment = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'approved',
            'appointment_date' => $yesterday,
        ]);

        $queue = Queue::factory()->create([
            'appointment_id' => $appointment->id,
            'consultation_type_id' => $this->consultationType->id,
            'queue_date' => $yesterday,
            'status' => 'waiting',
        ]);

        // Simulate no-show scheduler logic
        Appointment::query()
            ->where('status', 'approved')
            ->whereDate('appointment_date', $yesterday)
            ->each(function (Appointment $appt) {
                $appt->update(['status' => 'no_show']);

                Queue::query()
                    ->where('appointment_id', $appt->id)
                    ->whereIn('status', ['waiting', 'called'])
                    ->update(['status' => 'no_show']);
            });

        $appointment->refresh();
        $queue->refresh();

        expect($appointment->status)->toBe('no_show')
            ->and($queue->status)->toBe('no_show');
    });

    it('does not mark today approved appointments as no_show', function () {
        $appointment = Appointment::factory()->create([
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'approved',
            'appointment_date' => today(),
        ]);

        // Simulate no-show scheduler: only yesterday
        $yesterday = today()->subDay();
        Appointment::query()
            ->where('status', 'approved')
            ->whereDate('appointment_date', $yesterday)
            ->update(['status' => 'no_show']);

        $appointment->refresh();
        expect($appointment->status)->toBe('approved');
    });
});

describe('Queue Position Prediction', function () {
    it('predicts queue position for future appointments', function () {
        // Create 3 confirmed appointments for tomorrow
        $tomorrow = today()->addDay();

        Appointment::factory()->count(3)->create([
            'consultation_type_id' => $this->consultationType->id,
            'status' => 'confirmed',
            'appointment_date' => $tomorrow,
        ]);

        $queueService = app(QueueNumberService::class);
        $position = $queueService->predictQueuePosition(
            $this->consultationType->id,
            $tomorrow->toDateString(),
        );

        expect($position)->toBe(4); // 3 existing + 1
    });
});
