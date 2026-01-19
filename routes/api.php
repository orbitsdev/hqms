<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConsultationTypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes for the mobile application (Flutter).
| All routes are prefixed with /api and use Sanctum authentication.
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/user', [AuthController::class, 'user']);

    // Consultation Types & Availability
    Route::get('/consultation-types', [ConsultationTypeController::class, 'index']);
    Route::get('/doctors/availability', [ConsultationTypeController::class, 'doctorAvailability']);

    // Appointments
    Route::get('/appointments/my', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::put('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
});
