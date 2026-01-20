<?php

use App\Models\ConsultationType;
use App\Models\MedicalRecord;
use App\Models\PersonalInformation;
use App\Models\Prescription;
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

    // Create a doctor
    $this->doctor = User::factory()->create(['is_active' => true]);
    $this->doctor->assignRole('doctor');
    PersonalInformation::create([
        'user_id' => $this->doctor->id,
        'first_name' => 'Dr. Maria',
        'last_name' => 'Santos',
        'phone' => '09170000010',
    ]);

    // Create a nurse
    $this->nurse = User::factory()->create(['is_active' => true]);
    $this->nurse->assignRole('nurse');

    // Get consultation types
    $this->obType = ConsultationType::where('code', 'ob')->first();
    $this->pedType = ConsultationType::where('code', 'pedia')->first();
});

describe('GET /api/v1/medical-records/my', function () {
    it('returns user medical records', function () {
        // Create completed medical records
        MedicalRecord::factory()->count(3)->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'doctor_id' => $this->doctor->id,
            'nurse_id' => $this->nurse->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/medical-records/my');

        $response->assertOk()
            ->assertJsonStructure([
                'medical_records' => [
                    '*' => [
                        'id',
                        'patient' => [
                            'first_name',
                            'last_name',
                            'full_name',
                        ],
                        'visit' => [
                            'date',
                            'type',
                        ],
                        'vital_signs',
                        'examination',
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

        expect($response->json('medical_records'))->toHaveCount(3);
    });

    it('only returns completed records', function () {
        // Create completed record
        MedicalRecord::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
        ]);

        // Create in-progress record (should not be returned)
        MedicalRecord::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'status' => 'in_progress',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/medical-records/my');

        $response->assertOk();
        expect($response->json('medical_records'))->toHaveCount(1);
    });

    it('filters by consultation type', function () {
        MedicalRecord::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
        ]);
        MedicalRecord::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->pedType->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/medical-records/my?consultation_type_id={$this->obType->id}");

        $response->assertOk();
        expect($response->json('medical_records'))->toHaveCount(1);
        expect($response->json('medical_records.0.consultation_type.code'))->toBe('ob');
    });

    it('filters by date range', function () {
        MedicalRecord::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'visit_date' => now()->subDays(10),
        ]);
        MedicalRecord::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'visit_date' => now()->subDays(30),
        ]);

        $fromDate = now()->subDays(15)->format('Y-m-d');
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/medical-records/my?from_date={$fromDate}");

        $response->assertOk();
        expect($response->json('medical_records'))->toHaveCount(1);
    });

    it('does not return other users records', function () {
        $otherUser = User::factory()->create();

        MedicalRecord::factory()->completed()->create([
            'user_id' => $otherUser->id,
            'consultation_type_id' => $this->obType->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/medical-records/my');

        $response->assertOk();
        expect($response->json('medical_records'))->toHaveCount(0);
    });

    it('includes prescriptions in response', function () {
        $record = MedicalRecord::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'doctor_id' => $this->doctor->id,
        ]);

        Prescription::factory()->create([
            'medical_record_id' => $record->id,
            'prescribed_by' => $this->doctor->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/medical-records/my');

        $response->assertOk();
        expect($response->json('medical_records.0.prescriptions'))->toHaveCount(1);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/v1/medical-records/my');

        $response->assertUnauthorized();
    });
});

describe('GET /api/v1/medical-records/{id}', function () {
    it('returns medical record details', function () {
        $record = MedicalRecord::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'doctor_id' => $this->doctor->id,
            'nurse_id' => $this->nurse->id,
            'patient_first_name' => 'Maria',
            'patient_last_name' => 'Gonzales',
            'diagnosis' => 'Pregnancy Uterine 28 weeks',
        ]);

        Prescription::factory()->count(2)->create([
            'medical_record_id' => $record->id,
            'prescribed_by' => $this->doctor->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/medical-records/{$record->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'medical_record' => [
                    'id',
                    'patient' => [
                        'first_name',
                        'last_name',
                        'full_name',
                        'date_of_birth',
                        'gender',
                        'address',
                        'emergency_contact',
                    ],
                    'visit',
                    'chief_complaints',
                    'vital_signs',
                    'examination' => [
                        'diagnosis',
                        'plan',
                    ],
                    'prescriptions',
                    'consultation_type',
                    'doctor',
                ],
            ])
            ->assertJson([
                'medical_record' => [
                    'id' => $record->id,
                    'patient' => [
                        'first_name' => 'Maria',
                        'last_name' => 'Gonzales',
                    ],
                    'examination' => [
                        'diagnosis' => 'Pregnancy Uterine 28 weeks',
                    ],
                ],
            ]);

        expect($response->json('medical_record.prescriptions'))->toHaveCount(2);
    });

    it('returns 403 for other users record', function () {
        $otherUser = User::factory()->create();
        $record = MedicalRecord::factory()->completed()->create([
            'user_id' => $otherUser->id,
            'consultation_type_id' => $this->obType->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/medical-records/{$record->id}");

        $response->assertForbidden();
    });

    it('returns 404 for non-existent record', function () {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/medical-records/999999');

        $response->assertNotFound();
    });
});

describe('GET /api/v1/prescriptions/my', function () {
    it('returns user prescriptions from completed records', function () {
        $record = MedicalRecord::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'doctor_id' => $this->doctor->id,
        ]);

        Prescription::factory()->count(3)->create([
            'medical_record_id' => $record->id,
            'prescribed_by' => $this->doctor->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/prescriptions/my');

        $response->assertOk()
            ->assertJsonStructure([
                'prescriptions' => [
                    '*' => [
                        'id',
                        'medication_name',
                        'dosage',
                        'frequency',
                        'duration',
                        'instructions',
                        'quantity',
                        'is_hospital_drug',
                        'prescribed_by' => ['id', 'name'],
                        'visit_date',
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

        expect($response->json('prescriptions'))->toHaveCount(3);
    });

    it('does not return prescriptions from in-progress records', function () {
        $completedRecord = MedicalRecord::factory()->completed()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'doctor_id' => $this->doctor->id,
        ]);
        Prescription::factory()->create([
            'medical_record_id' => $completedRecord->id,
            'prescribed_by' => $this->doctor->id,
        ]);

        $inProgressRecord = MedicalRecord::factory()->create([
            'user_id' => $this->patient->id,
            'consultation_type_id' => $this->obType->id,
            'status' => 'in_progress',
        ]);
        Prescription::factory()->create([
            'medical_record_id' => $inProgressRecord->id,
            'prescribed_by' => $this->doctor->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/prescriptions/my');

        $response->assertOk();
        expect($response->json('prescriptions'))->toHaveCount(1);
    });

    it('does not return other users prescriptions', function () {
        $otherUser = User::factory()->create();
        $record = MedicalRecord::factory()->completed()->create([
            'user_id' => $otherUser->id,
            'consultation_type_id' => $this->obType->id,
        ]);
        Prescription::factory()->create([
            'medical_record_id' => $record->id,
            'prescribed_by' => $this->doctor->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/prescriptions/my');

        $response->assertOk();
        expect($response->json('prescriptions'))->toHaveCount(0);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/v1/prescriptions/my');

        $response->assertUnauthorized();
    });
});
