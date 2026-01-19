<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\PersonalInformation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new patient user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            // Create user
            $user = User::create([
                'email' => $validated['email'],
                'password' => $validated['password'],
                'is_active' => true,
            ]);

            // Assign patient role
            $user->assignRole('patient');

            // Create personal information
            PersonalInformation::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'marital_status' => $validated['marital_status'] ?? null,
                'province' => $validated['province'] ?? null,
                'municipality' => $validated['municipality'] ?? null,
                'barangay' => $validated['barangay'] ?? null,
                'street' => $validated['street'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            ]);

            return $user;
        });

        // Generate token
        $deviceName = $request->input('device_name', 'mobile');
        $token = $user->createToken($deviceName)->plainTextToken;

        // Load relationships
        $user->load('personalInformation', 'roles');

        return response()->json([
            'message' => 'Registration successful.',
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login user and create token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Attempt authentication
        if (! Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $validated['email'])->first();

        // Check if user is active
        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact support.'],
            ]);
        }

        // Generate token
        $deviceName = $request->input('device_name', 'mobile');
        $token = $user->createToken($deviceName)->plainTextToken;

        // Load relationships
        $user->load('personalInformation', 'roles');

        return response()->json([
            'message' => 'Login successful.',
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout user (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Logout from all devices (revoke all tokens).
     */
    public function logoutAll(Request $request): JsonResponse
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully.',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('personalInformation', 'roles');

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }
}
