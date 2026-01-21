<?php

use App\Models\Appointment;
use App\Models\ConsultationType;
use App\Models\PersonalInformation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $this->seed(\Database\Seeders\ConsultationTypeSeeder::class);

    // Create a test patient
    $this->patient = User::factory()->create(['is_active' => true]);
    $this->patient->assignRole('patient');
    PersonalInformation::create([
        'user_id' => $this->patient->id,
        'first_name' => 'Test',
        'last_name' => 'Patient',
        'phone' => '09171234567',
    ]);
    $this->token = $this->patient->createToken('mobile')->plainTextToken;

    // Get consultation types
    $this->obType = ConsultationType::where('code', 'ob')->first();
    $this->pediaType = ConsultationType::where('code', 'pedia')->first();

    // Create a doctor with schedules for testing availability
    $this->doctor = User::factory()->create(['is_active' => true]);
    $this->doctor->assignRole('doctor');
    $this->doctor->consultationTypes()->attach([$this->obType->id, $this->pediaType->id]);

    // Create regular schedules for all days of the week (Mon-Sat)
    foreach ([1, 2, 3, 4, 5, 6] as $dayOfWeek) {
        \App\Models\DoctorSchedule::create([
            'user_id' => $this->doctor->id,
            'consultation_type_id' => $this->obType->id,
            'schedule_type' => 'regular',
            'day_of_week' => $dayOfWeek,
        ]);
        \App\Models\DoctorSchedule::create([
            'user_id' => $this->doctor->id,
            'consultation_type_id' => $this->pediaType->id,
            'schedule_type' => 'regular',
            'day_of_week' => $dayOfWeek,
        ]);
    }
});

describe('GET /api/v1/consultation-types', function () {
    it('returns all active consultation types with availability', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/consultation-types');

        $response->assertOk()
            ->assertJsonStructure([
                'consultation_types' => [
                    '*' => [
                        'id',
                        'code',
                        'name',
                        'short_name',
                        'avg_duration',
                        'is_available',
                        'is_active',
                    ],
                ],
            ]);

        expect($response->json('consultation_types'))->toHaveCount(3);
    });

    it('returns availability for a specific date', function () {
        $futureDate = now()->addDays(3)->toDateString();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/consultation-types?date={$futureDate}");

        $response->assertOk();

        $types = $response->json('consultation_types');
        foreach ($types as $type) {
            expect($type['query_date'])->toBe($futureDate);
        }
    });

    it('shows reduced availability when appointments exist', function () {
        // Create some appointments for today
        $today = today()->toDateString();
        for ($i = 0; $i < 5; $i++) {
            Appointment::create([
                'user_id' => $this->patient->id,
                'consultation_type_id' => $this->obType->id,
                'appointment_date' => $today,
                'status' => 'approved',
                'source' => 'online',
            ]);
        }

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/consultation-types?date='.$today);

        $response->assertOk();

        $obType = collect($response->json('consultation_types'))
            ->firstWhere('code', 'ob');

        expect($obType['booked_count'])->toBe(5);
        expect($obType['is_available'])->toBeTrue(); // No limit - availability based on doctor schedule
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/v1/consultation-types');

        $response->assertUnauthorized();
    });
});

describe('GET /api/v1/doctors/availability', function () {
    it('returns doctor availability for a consultation type', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/doctors/availability?consultation_type_id={$this->obType->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'consultation_type' => ['id', 'name', 'code'],
                'date',
                'is_available',
                'doctors',
            ]);

        expect($response->json('is_available'))->toBeTrue();
        expect($response->json('doctors'))->toBeArray();
    });

    it('requires consultation_type_id parameter', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/doctors/availability');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['consultation_type_id']);
    });

    it('validates consultation_type_id exists', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/doctors/availability?consultation_type_id=999');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['consultation_type_id']);
    });
});

describe('POST /api/v1/appointments', function () {
    it('creates an appointment with valid data', function () {
        $futureDate = now()->addDays(2)->toDateString();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/appointments', [
                'consultation_type_id' => $this->obType->id,
                'appointment_date' => $futureDate,
                'chief_complaints' => 'Regular prenatal checkup',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'appointment' => [
                    'id',
                    'appointment_date',
                    'chief_complaints',
                    'status',
                    'source',
                    'consultation_type' => ['id', 'name', 'code'],
                ],
            ])
            ->assertJson([
                'message' => 'Appointment request submitted successfully. Please wait for confirmation.',
                'appointment' => [
                    'status' => 'pending',
                    'source' => 'online',
                    'chief_complaints' => 'Regular prenatal checkup',
                ],
            ]);

        // Verify appointment was created
        $appointment = Appointment::where('user_id', $this->patient->id)
            ->where('consultation_type_id', $this->obType->id)
            ->where('status', 'pending')
            ->first();

        expect($appointment)->not->toBeNull();
        expect($appointment->appointment_date->toDateString())->toBe($futureDate);
        expect($appointment->source)->toBe('online');
    });

    it('creates appointment for today', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/appointments', [
                'consultation_type_id' => $this->pediaType->id,
                'appointment_date' => today()->toDateString(),
            ]);

        $response->assertCreated();
    });

    it('fails with missing required fields', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/appointments', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['consultation_type_id', 'appointment_date']);
    });

    it('fails with invalid consultation type', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/appointments', [
                'consultation_type_id' => 999,
                'appointment_date' => now()->addDay()->toDateString(),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['consultation_type_id']);
    });

    it('fails with past date', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/appointments', [
                'consultation_type_id' => $this->obType->id,
                'appointment_date' => now()->subDay()->toDateString(),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['appointment_date']);
    });

    it('prevents duplicate appointment for same type and date', function () {
        $futureDate = now()->addDays(2)->format('Y-m-d');

        // Create first appointment via API
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/appointments', [
                'consultation_type_id' => $this->obType->id,
                'appointment_date' => $futureDate,
            ])
            ->assertCreated();

        // Try to create duplicate
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/appointments', [
                'consultation_type_id' => $this->obType->id,
                'appointment_date' => $futureDate,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'duplicate_appointment',
            ]);
    });

    it('allows different consultation types on same date', function () {
        $futureDate = now()->addDays(2)->toDateString();

        // Create OB appointment
        Appointment::create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'appointment_date' => $futureDate,
            'status' => 'pending',
            'source' => 'online',
        ]);

        // Create PEDIA appointment on same date
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/appointments', [
                'consultation_type_id' => $this->pediaType->id,
                'appointment_date' => $futureDate,
            ]);

        $response->assertCreated();
    });
});

describe('GET /api/v1/appointments/my', function () {
    it('returns user appointments', function () {
        // Create some appointments
        Appointment::factory()->count(3)->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'appointment_date' => now()->addDays(1),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/appointments/my');

        $response->assertOk()
            ->assertJsonStructure([
                'appointments' => [
                    '*' => [
                        'id',
                        'appointment_date',
                        'status',
                        'consultation_type',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);

        expect($response->json('appointments'))->toHaveCount(3);
    });

    it('filters by status', function () {
        Appointment::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'status' => 'pending',
        ]);
        Appointment::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'status' => 'approved',
        ]);
        Appointment::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'status' => 'cancelled',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/appointments/my?status=pending,approved');

        $response->assertOk();
        expect($response->json('appointments'))->toHaveCount(2);
    });

    it('filters upcoming only', function () {
        Appointment::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'appointment_date' => now()->subDays(5),
            'status' => 'completed',
        ]);
        Appointment::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'appointment_date' => now()->addDays(5),
            'status' => 'approved',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/appointments/my?upcoming=true');

        $response->assertOk();
        expect($response->json('appointments'))->toHaveCount(1);
    });

    it('does not return other users appointments', function () {
        $otherUser = User::factory()->create();

        Appointment::factory()->create([
            'user_id' => $otherUser->id,
            'consultation_type_id' => $this->obType->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/appointments/my');

        $response->assertOk();
        expect($response->json('appointments'))->toHaveCount(0);
    });
});

describe('GET /api/v1/appointments/{id}', function () {
    it('returns appointment details', function () {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'chief_complaints' => 'Test complaints',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/appointments/{$appointment->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'appointment' => [
                    'id',
                    'appointment_date',
                    'chief_complaints',
                    'status',
                    'consultation_type',
                ],
            ])
            ->assertJson([
                'appointment' => [
                    'id' => $appointment->id,
                    'chief_complaints' => 'Test complaints',
                ],
            ]);
    });

    it('returns 403 for other users appointment', function () {
        $otherUser = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $otherUser->id,
            'consultation_type_id' => $this->obType->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/appointments/{$appointment->id}");

        $response->assertForbidden();
    });

    it('returns 404 for non-existent appointment', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/appointments/999999');

        $response->assertNotFound();
    });
});

describe('PUT /api/v1/appointments/{id}/cancel', function () {
    it('cancels a pending appointment', function () {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'appointment_date' => now()->addDays(2),
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/appointments/{$appointment->id}/cancel", [
                'reason' => 'Cannot attend due to emergency',
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Appointment cancelled successfully.',
                'appointment' => [
                    'id' => $appointment->id,
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Cannot attend due to emergency',
                ],
            ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'Cannot attend due to emergency',
        ]);
    });

    it('cancels an approved appointment', function () {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'appointment_date' => now()->addDays(2),
            'status' => 'approved',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/appointments/{$appointment->id}/cancel");

        $response->assertOk();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
    });

    it('fails to cancel completed appointment', function () {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'status' => 'completed',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/appointments/{$appointment->id}/cancel");

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'invalid_status',
            ]);
    });

    it('fails to cancel past appointment', function () {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'appointment_date' => now()->subDays(1),
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/appointments/{$appointment->id}/cancel");

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'past_appointment',
            ]);
    });

    it('returns 403 for other users appointment', function () {
        $otherUser = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $otherUser->id,
            'consultation_type_id' => $this->obType->id,
            'appointment_date' => now()->addDays(2),
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/appointments/{$appointment->id}/cancel");

        $response->assertForbidden();
    });
});
