<?php

use App\Models\NotificationLog;
use App\Models\PersonalInformation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);

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
});

describe('GET /api/v1/notifications', function () {
    it('returns user notifications', function () {
        NotificationLog::factory()->count(5)->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'notifications' => [
                    '*' => [
                        'id',
                        'type',
                        'title',
                        'body',
                        'is_read',
                        'created_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);

        expect($response->json('notifications'))->toHaveCount(5);
    });

    it('returns only sent notifications', function () {
        NotificationLog::factory()->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        NotificationLog::factory()->pending()->create([
            'user_id' => $this->patient->id,
        ]);
        NotificationLog::factory()->failed()->create([
            'user_id' => $this->patient->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/notifications');

        $response->assertOk();
        expect($response->json('notifications'))->toHaveCount(1);
    });

    it('filters unread only', function () {
        NotificationLog::factory()->unread()->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        NotificationLog::factory()->read()->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/notifications?unread_only=true');

        $response->assertOk();
        expect($response->json('notifications'))->toHaveCount(1);
        expect($response->json('notifications.0.is_read'))->toBeFalse();
    });

    it('filters by notification type', function () {
        NotificationLog::factory()->appointmentApproved()->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        NotificationLog::factory()->queueCalled()->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/notifications?type=appointment_approved');

        $response->assertOk();
        expect($response->json('notifications'))->toHaveCount(1);
        expect($response->json('notifications.0.type'))->toBe('appointment_approved');
    });

    it('does not return other users notifications', function () {
        $otherUser = User::factory()->create();
        NotificationLog::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/notifications');

        $response->assertOk();
        expect($response->json('notifications'))->toHaveCount(0);
    });

    it('orders notifications by created_at desc', function () {
        NotificationLog::factory()->create([
            'user_id' => $this->patient->id,
            'title' => 'First',
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now()->subHours(2),
        ]);
        NotificationLog::factory()->create([
            'user_id' => $this->patient->id,
            'title' => 'Second',
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now()->subHour(),
        ]);
        NotificationLog::factory()->create([
            'user_id' => $this->patient->id,
            'title' => 'Third',
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/notifications');

        $response->assertOk();
        expect($response->json('notifications.0.title'))->toBe('Third');
        expect($response->json('notifications.1.title'))->toBe('Second');
        expect($response->json('notifications.2.title'))->toBe('First');
    });

    it('paginates notifications', function () {
        NotificationLog::factory()->count(25)->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/notifications?per_page=10');

        $response->assertOk();
        expect($response->json('notifications'))->toHaveCount(10);
        expect($response->json('meta.total'))->toBe(25);
        expect($response->json('meta.last_page'))->toBe(3);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/v1/notifications');

        $response->assertUnauthorized();
    });
});

describe('PUT /api/v1/notifications/{id}/read', function () {
    it('marks notification as read', function () {
        $notification = NotificationLog::factory()->unread()->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJson([
                'message' => 'Notification marked as read.',
                'notification' => [
                    'id' => $notification->id,
                    'is_read' => true,
                ],
            ]);

        $this->assertDatabaseHas('notification_logs', [
            'id' => $notification->id,
        ]);

        $notification->refresh();
        expect($notification->read_at)->not->toBeNull();
    });

    it('does not change already read notification', function () {
        $readAt = now()->subHour();
        $notification = NotificationLog::factory()->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
            'read_at' => $readAt,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertOk();

        $notification->refresh();
        expect($notification->read_at->format('Y-m-d H:i:s'))->toBe($readAt->format('Y-m-d H:i:s'));
    });

    it('returns 403 for other users notification', function () {
        $otherUser = User::factory()->create();
        $notification = NotificationLog::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'sent',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertForbidden();
    });

    it('returns 404 for non-existent notification', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson('/api/v1/notifications/999999/read');

        $response->assertNotFound();
    });
});

describe('PUT /api/v1/notifications/read-all', function () {
    it('marks all notifications as read', function () {
        NotificationLog::factory()->unread()->count(5)->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson('/api/v1/notifications/read-all');

        $response->assertOk()
            ->assertJson([
                'message' => 'All notifications marked as read.',
                'updated_count' => 5,
            ]);

        $unreadCount = NotificationLog::forUser($this->patient->id)->unread()->count();
        expect($unreadCount)->toBe(0);
    });

    it('only marks unread notifications', function () {
        NotificationLog::factory()->unread()->count(3)->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        NotificationLog::factory()->read()->count(2)->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson('/api/v1/notifications/read-all');

        $response->assertOk()
            ->assertJson([
                'updated_count' => 3,
            ]);
    });

    it('does not affect other users notifications', function () {
        $otherUser = User::factory()->create();
        NotificationLog::factory()->unread()->count(3)->create([
            'user_id' => $otherUser->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        NotificationLog::factory()->unread()->count(2)->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson('/api/v1/notifications/read-all');

        $response->assertOk()
            ->assertJson([
                'updated_count' => 2,
            ]);

        $otherUserUnread = NotificationLog::forUser($otherUser->id)->unread()->count();
        expect($otherUserUnread)->toBe(3);
    });

    it('returns 0 when no unread notifications', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson('/api/v1/notifications/read-all');

        $response->assertOk()
            ->assertJson([
                'updated_count' => 0,
            ]);
    });
});

describe('GET /api/v1/notifications/unread-count', function () {
    it('returns unread notification count', function () {
        NotificationLog::factory()->unread()->count(5)->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        NotificationLog::factory()->read()->count(3)->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/notifications/unread-count');

        $response->assertOk()
            ->assertJson([
                'unread_count' => 5,
            ]);
    });

    it('returns 0 when no unread notifications', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/notifications/unread-count');

        $response->assertOk()
            ->assertJson([
                'unread_count' => 0,
            ]);
    });

    it('only counts sent notifications', function () {
        NotificationLog::factory()->unread()->create([
            'user_id' => $this->patient->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        NotificationLog::factory()->pending()->create([
            'user_id' => $this->patient->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/notifications/unread-count');

        $response->assertOk()
            ->assertJson([
                'unread_count' => 1,
            ]);
    });

    it('does not count other users notifications', function () {
        $otherUser = User::factory()->create();
        NotificationLog::factory()->unread()->count(10)->create([
            'user_id' => $otherUser->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/notifications/unread-count');

        $response->assertOk()
            ->assertJson([
                'unread_count' => 0,
            ]);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/v1/notifications/unread-count');

        $response->assertUnauthorized();
    });
});
