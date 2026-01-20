<?php

use App\Models\ConsultationType;
use App\Models\PersonalInformation;
use App\Models\Queue;
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
});

describe('GET /api/v1/queues/active', function () {
    it('returns active queue for today', function () {
        $queue = Queue::factory()->waiting()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_number' => 5,
            'queue_date' => today(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/active');

        $response->assertOk()
            ->assertJsonStructure([
                'queue' => [
                    'id',
                    'queue_number',
                    'formatted_number',
                    'queue_date',
                    'status',
                    'priority',
                    'consultation_type',
                ],
                'current_serving',
                'ahead_count',
            ])
            ->assertJson([
                'queue' => [
                    'id' => $queue->id,
                    'queue_number' => 5,
                    'status' => 'waiting',
                ],
            ]);
    });

    it('returns null when no active queue', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/active');

        $response->assertOk()
            ->assertJson([
                'message' => 'No active queue found.',
                'queue' => null,
            ]);
    });

    it('returns queue when status is called', function () {
        $queue = Queue::factory()->called()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_date' => today(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/active');

        $response->assertOk()
            ->assertJson([
                'queue' => [
                    'id' => $queue->id,
                    'status' => 'called',
                ],
            ]);
    });

    it('returns queue when status is serving', function () {
        $queue = Queue::factory()->serving()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_date' => today(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/active');

        $response->assertOk()
            ->assertJson([
                'queue' => [
                    'id' => $queue->id,
                    'status' => 'serving',
                ],
            ]);
    });

    it('does not return completed queue', function () {
        Queue::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_date' => today(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/active');

        $response->assertOk()
            ->assertJson([
                'message' => 'No active queue found.',
                'queue' => null,
            ]);
    });

    it('calculates ahead count correctly', function () {
        // Create queues ahead
        Queue::factory()->waiting()->create([
            'user_id' => User::factory()->create()->id,
            'consultation_type_id' => $this->obType->id,
            'queue_number' => 1,
            'queue_date' => today(),
        ]);
        Queue::factory()->waiting()->create([
            'user_id' => User::factory()->create()->id,
            'consultation_type_id' => $this->obType->id,
            'queue_number' => 2,
            'queue_date' => today(),
        ]);

        // Patient's queue
        Queue::factory()->waiting()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_number' => 3,
            'queue_date' => today(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/active');

        $response->assertOk()
            ->assertJson([
                'ahead_count' => 2,
            ]);
    });

    it('shows current serving number', function () {
        // Create serving queue
        Queue::factory()->serving()->create([
            'user_id' => User::factory()->create()->id,
            'consultation_type_id' => $this->obType->id,
            'queue_number' => 1,
            'queue_date' => today(),
        ]);

        // Patient's waiting queue
        Queue::factory()->waiting()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_number' => 2,
            'queue_date' => today(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/active');

        $response->assertOk()
            ->assertJsonStructure([
                'current_serving' => [
                    'queue_number',
                    'formatted_number',
                ],
            ])
            ->assertJson([
                'current_serving' => [
                    'queue_number' => 1,
                ],
            ]);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/v1/queues/active');

        $response->assertUnauthorized();
    });
});

describe('GET /api/v1/queues/my', function () {
    it('returns all queues for today', function () {
        Queue::factory()->count(2)->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_date' => today(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/my');

        $response->assertOk()
            ->assertJsonStructure([
                'queues' => [
                    '*' => [
                        'id',
                        'queue_number',
                        'formatted_number',
                        'status',
                    ],
                ],
            ]);

        expect($response->json('queues'))->toHaveCount(2);
    });

    it('returns empty array when no queues for today', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/my');

        $response->assertOk();
        expect($response->json('queues'))->toHaveCount(0);
    });

    it('does not return other users queues', function () {
        $otherUser = User::factory()->create();
        Queue::factory()->create([
            'user_id' => $otherUser->id,
            'consultation_type_id' => $this->obType->id,
            'queue_date' => today(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/my');

        $response->assertOk();
        expect($response->json('queues'))->toHaveCount(0);
    });
});

describe('GET /api/v1/queues/history', function () {
    it('returns queue history', function () {
        Queue::factory()->completed()->count(3)->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_date' => now()->subDays(5),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/history');

        $response->assertOk()
            ->assertJsonStructure([
                'queues' => [
                    '*' => [
                        'id',
                        'queue_number',
                        'queue_date',
                        'status',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);

        expect($response->json('queues'))->toHaveCount(3);
    });

    it('filters by date range', function () {
        Queue::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_date' => now()->subDays(5),
        ]);
        Queue::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_date' => now()->subDays(15),
        ]);

        $fromDate = now()->subDays(10)->format('Y-m-d');
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/queues/history?from_date={$fromDate}");

        $response->assertOk();
        expect($response->json('queues'))->toHaveCount(1);
    });

    it('filters by status', function () {
        Queue::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_date' => today(),
        ]);
        Queue::factory()->skipped()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_date' => today(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/history?status=completed');

        $response->assertOk();
        expect($response->json('queues'))->toHaveCount(1);
    });
});

describe('GET /api/v1/queues/{id}', function () {
    it('returns queue details', function () {
        $queue = Queue::factory()->waiting()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'queue_number' => 5,
            'queue_date' => today(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/queues/{$queue->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'queue' => [
                    'id',
                    'queue_number',
                    'formatted_number',
                    'status',
                    'consultation_type',
                ],
            ])
            ->assertJson([
                'queue' => [
                    'id' => $queue->id,
                    'queue_number' => 5,
                ],
            ]);
    });

    it('returns 403 for other users queue', function () {
        $otherUser = User::factory()->create();
        $queue = Queue::factory()->create([
            'user_id' => $otherUser->id,
            'consultation_type_id' => $this->obType->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/queues/{$queue->id}");

        $response->assertForbidden();
    });

    it('returns 404 for non-existent queue', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/queues/999999');

        $response->assertNotFound();
    });
});
