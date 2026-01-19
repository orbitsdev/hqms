<?php

use App\Models\PersonalInformation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles
    $this->seed(\Database\Seeders\RoleSeeder::class);
});

describe('POST /api/v1/register', function () {
    it('registers a new patient with valid data', function () {
        $response = $this->postJson('/api/v1/register', [
            'email' => 'newpatient@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'phone' => '09171234567',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'email',
                    'roles',
                    'personal_information' => [
                        'first_name',
                        'last_name',
                        'phone',
                    ],
                ],
                'token',
                'token_type',
            ])
            ->assertJson([
                'message' => 'Registration successful.',
                'token_type' => 'Bearer',
            ]);

        // Verify user was created
        $this->assertDatabaseHas('users', ['email' => 'newpatient@test.com']);
        $this->assertDatabaseHas('personal_information', [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'phone' => '09171234567',
        ]);

        // Verify patient role assigned
        $user = User::where('email', 'newpatient@test.com')->first();
        expect($user->hasRole('patient'))->toBeTrue();
    });

    it('registers patient with all optional fields', function () {
        $response = $this->postJson('/api/v1/register', [
            'email' => 'fullpatient@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'first_name' => 'Maria',
            'middle_name' => 'Santos',
            'last_name' => 'Garcia',
            'phone' => '09181234567',
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'marital_status' => 'married',
            'province' => 'Sultan Kudarat',
            'municipality' => 'Tacurong City',
            'barangay' => 'Poblacion',
            'street' => '123 Main St',
            'occupation' => 'Teacher',
            'emergency_contact_name' => 'Jose Garcia',
            'emergency_contact_phone' => '09191234567',
            'device_name' => 'iPhone 15',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('personal_information', [
            'first_name' => 'Maria',
            'middle_name' => 'Santos',
            'last_name' => 'Garcia',
            'gender' => 'female',
            'province' => 'Sultan Kudarat',
        ]);
    });

    it('fails registration with missing required fields', function () {
        $response = $this->postJson('/api/v1/register', [
            'email' => 'test@test.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password', 'first_name', 'last_name', 'phone']);
    });

    it('fails registration with invalid email', function () {
        $response = $this->postJson('/api/v1/register', [
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '09171234567',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('fails registration with duplicate email', function () {
        User::factory()->create(['email' => 'existing@test.com']);

        $response = $this->postJson('/api/v1/register', [
            'email' => 'existing@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '09171234567',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('fails registration with duplicate phone', function () {
        $user = User::factory()->create();
        PersonalInformation::create([
            'user_id' => $user->id,
            'first_name' => 'Existing',
            'last_name' => 'User',
            'phone' => '09171234567',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'email' => 'newuser@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '09171234567',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    });

    it('fails registration with password mismatch', function () {
        $response = $this->postJson('/api/v1/register', [
            'email' => 'test@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword!',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '09171234567',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });
});

describe('POST /api/v1/login', function () {
    it('logs in with valid credentials', function () {
        $user = User::factory()->create([
            'email' => 'patient@test.com',
            'password' => 'Password123!',
            'is_active' => true,
        ]);
        $user->assignRole('patient');
        PersonalInformation::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'phone' => '09171234567',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'patient@test.com',
            'password' => 'Password123!',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'email',
                    'roles',
                    'personal_information',
                ],
                'token',
                'token_type',
            ])
            ->assertJson([
                'message' => 'Login successful.',
                'token_type' => 'Bearer',
            ]);

        // Verify token was created
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'mobile',
        ]);
    });

    it('logs in with custom device name', function () {
        $user = User::factory()->create([
            'email' => 'patient@test.com',
            'password' => 'Password123!',
            'is_active' => true,
        ]);
        $user->assignRole('patient');
        PersonalInformation::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'phone' => '09171234567',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'patient@test.com',
            'password' => 'Password123!',
            'device_name' => 'Samsung Galaxy S24',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'Samsung Galaxy S24',
        ]);
    });

    it('fails login with invalid credentials', function () {
        User::factory()->create([
            'email' => 'patient@test.com',
            'password' => 'Password123!',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'patient@test.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('fails login with non-existent email', function () {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'Password123!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('fails login for deactivated user', function () {
        $user = User::factory()->create([
            'email' => 'inactive@test.com',
            'password' => 'Password123!',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'inactive@test.com',
            'password' => 'Password123!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('fails login with missing fields', function () {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    });
});

describe('POST /api/v1/logout', function () {
    it('logs out authenticated user', function () {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $token = $user->createToken('mobile')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Logged out successfully.']);

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    });

    it('fails logout without authentication', function () {
        $response = $this->postJson('/api/v1/logout');

        $response->assertUnauthorized();
    });
});

describe('POST /api/v1/logout-all', function () {
    it('logs out from all devices', function () {
        $user = User::factory()->create();
        $user->assignRole('patient');

        // Create multiple tokens
        $user->createToken('mobile');
        $user->createToken('tablet');
        $token = $user->createToken('web')->plainTextToken;

        // Verify 3 tokens exist
        expect($user->tokens()->count())->toBe(3);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout-all');

        $response->assertOk()
            ->assertJson(['message' => 'Logged out from all devices successfully.']);

        // Verify all tokens were deleted
        expect($user->tokens()->count())->toBe(0);
    });
});

describe('GET /api/v1/user', function () {
    it('returns authenticated user data', function () {
        $user = User::factory()->create([
            'email' => 'testuser@test.com',
            'is_active' => true,
        ]);
        $user->assignRole('patient');
        PersonalInformation::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '09171234567',
            'gender' => 'male',
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/user');

        $response->assertOk()
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'email',
                    'is_active',
                    'roles',
                    'personal_information' => [
                        'first_name',
                        'last_name',
                        'full_name',
                        'phone',
                        'gender',
                    ],
                ],
            ])
            ->assertJson([
                'user' => [
                    'email' => 'testuser@test.com',
                    'personal_information' => [
                        'first_name' => 'Test',
                        'last_name' => 'User',
                        'full_name' => 'Test User',
                    ],
                ],
            ]);
    });

    it('fails without authentication', function () {
        $response = $this->getJson('/api/v1/user');

        $response->assertUnauthorized();
    });

    it('fails with invalid token', function () {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/v1/user');

        $response->assertUnauthorized();
    });
});
