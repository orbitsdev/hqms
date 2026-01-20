<?php

use App\Models\PersonalInformation;
use App\Models\User;
use App\Models\UserDevice;
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

describe('POST /api/v1/devices/register', function () {
    it('registers a new device', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/devices/register', [
                'device_id' => 'test-device-123',
                'fcm_token' => 'fcm-token-abc123',
                'platform' => 'android',
                'device_model' => 'Samsung Galaxy S24',
                'app_version' => '1.0.0',
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'device' => [
                    'id',
                    'device_id',
                    'device_model',
                    'platform',
                    'app_version',
                    'is_active',
                    'last_used_at',
                ],
            ])
            ->assertJson([
                'message' => 'Device registered successfully.',
                'device' => [
                    'device_id' => 'test-device-123',
                    'platform' => 'android',
                    'device_model' => 'Samsung Galaxy S24',
                    'is_active' => true,
                ],
            ]);

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $this->patient->id,
            'device_id' => 'test-device-123',
            'fcm_token' => 'fcm-token-abc123',
            'platform' => 'android',
        ]);
    });

    it('updates existing device for same user', function () {
        UserDevice::factory()->create([
            'user_id' => $this->patient->id,
            'device_id' => 'existing-device',
            'fcm_token' => 'old-token',
            'platform' => 'android',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/devices/register', [
                'device_id' => 'existing-device',
                'fcm_token' => 'new-token',
                'platform' => 'android',
                'app_version' => '2.0.0',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $this->patient->id,
            'device_id' => 'existing-device',
            'fcm_token' => 'new-token',
            'app_version' => '2.0.0',
        ]);

        // Should only have one device record
        expect(UserDevice::where('device_id', 'existing-device')->count())->toBe(1);
    });

    it('removes device from other users when registered', function () {
        $otherUser = User::factory()->create();
        UserDevice::factory()->create([
            'user_id' => $otherUser->id,
            'device_id' => 'shared-device',
            'fcm_token' => 'other-token',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/devices/register', [
                'device_id' => 'shared-device',
                'fcm_token' => 'my-token',
                'platform' => 'ios',
            ]);

        $response->assertOk();

        // Device should be removed from other user
        $this->assertDatabaseMissing('user_devices', [
            'user_id' => $otherUser->id,
            'device_id' => 'shared-device',
        ]);

        // Device should belong to current user
        $this->assertDatabaseHas('user_devices', [
            'user_id' => $this->patient->id,
            'device_id' => 'shared-device',
        ]);
    });

    it('requires device_id and fcm_token', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/devices/register', [
                'platform' => 'android',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['device_id', 'fcm_token']);
    });

    it('validates platform values', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/devices/register', [
                'device_id' => 'test-device',
                'fcm_token' => 'test-token',
                'platform' => 'windows',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['platform']);
    });

    it('requires authentication', function () {
        $response = $this->postJson('/api/v1/devices/register', [
            'device_id' => 'test-device',
            'fcm_token' => 'test-token',
            'platform' => 'android',
        ]);

        $response->assertUnauthorized();
    });
});

describe('POST /api/v1/devices/logout', function () {
    it('deactivates a device', function () {
        UserDevice::factory()->create([
            'user_id' => $this->patient->id,
            'device_id' => 'my-device',
            'fcm_token' => 'my-token',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/devices/logout', [
                'device_id' => 'my-device',
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Device logged out successfully.',
            ]);

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $this->patient->id,
            'device_id' => 'my-device',
            'is_active' => false,
            'fcm_token' => null,
        ]);
    });

    it('returns 404 for non-existent device', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/devices/logout', [
                'device_id' => 'non-existent-device',
            ]);

        $response->assertNotFound();
    });

    it('cannot logout other users device', function () {
        $otherUser = User::factory()->create();
        UserDevice::factory()->create([
            'user_id' => $otherUser->id,
            'device_id' => 'other-device',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/devices/logout', [
                'device_id' => 'other-device',
            ]);

        $response->assertNotFound();
    });
});

describe('PUT /api/v1/devices/token', function () {
    it('updates FCM token', function () {
        UserDevice::factory()->create([
            'user_id' => $this->patient->id,
            'device_id' => 'my-device',
            'fcm_token' => 'old-token',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson('/api/v1/devices/token', [
                'device_id' => 'my-device',
                'fcm_token' => 'new-refreshed-token',
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'FCM token updated successfully.',
            ]);

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $this->patient->id,
            'device_id' => 'my-device',
            'fcm_token' => 'new-refreshed-token',
        ]);
    });

    it('returns 404 for non-registered device', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson('/api/v1/devices/token', [
                'device_id' => 'unregistered-device',
                'fcm_token' => 'new-token',
            ]);

        $response->assertNotFound()
            ->assertJson([
                'message' => 'Device not found. Please register the device first.',
            ]);
    });
});

describe('GET /api/v1/devices', function () {
    it('lists all user devices', function () {
        UserDevice::factory()->count(3)->create([
            'user_id' => $this->patient->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/devices');

        $response->assertOk()
            ->assertJsonStructure([
                'devices' => [
                    '*' => [
                        'id',
                        'device_id',
                        'device_model',
                        'platform',
                        'is_active',
                    ],
                ],
            ]);

        expect($response->json('devices'))->toHaveCount(3);
    });

    it('does not list other users devices', function () {
        $otherUser = User::factory()->create();
        UserDevice::factory()->count(5)->create([
            'user_id' => $otherUser->id,
        ]);

        UserDevice::factory()->count(2)->create([
            'user_id' => $this->patient->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/devices');

        $response->assertOk();
        expect($response->json('devices'))->toHaveCount(2);
    });

    it('orders by last_used_at desc', function () {
        UserDevice::factory()->create([
            'user_id' => $this->patient->id,
            'device_model' => 'Old Device',
            'last_used_at' => now()->subDays(5),
        ]);
        UserDevice::factory()->create([
            'user_id' => $this->patient->id,
            'device_model' => 'New Device',
            'last_used_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/devices');

        $response->assertOk();
        expect($response->json('devices.0.device_model'))->toBe('New Device');
        expect($response->json('devices.1.device_model'))->toBe('Old Device');
    });
});

describe('DELETE /api/v1/devices/{deviceId}', function () {
    it('removes a device', function () {
        UserDevice::factory()->create([
            'user_id' => $this->patient->id,
            'device_id' => 'device-to-remove',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson('/api/v1/devices/device-to-remove');

        $response->assertOk()
            ->assertJson([
                'message' => 'Device removed successfully.',
            ]);

        $this->assertDatabaseMissing('user_devices', [
            'device_id' => 'device-to-remove',
        ]);
    });

    it('returns 404 for non-existent device', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson('/api/v1/devices/non-existent');

        $response->assertNotFound();
    });

    it('cannot remove other users device', function () {
        $otherUser = User::factory()->create();
        UserDevice::factory()->create([
            'user_id' => $otherUser->id,
            'device_id' => 'other-device',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson('/api/v1/devices/other-device');

        $response->assertNotFound();

        // Device should still exist
        $this->assertDatabaseHas('user_devices', [
            'device_id' => 'other-device',
        ]);
    });
});
