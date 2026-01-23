<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConsultationTypeController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\SmsController;
use App\Http\Controllers\Api\UserDeviceController;
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

    
});

/*
|--------------------------------------------------------------------------
| SMS Routes (for development/testing)
|--------------------------------------------------------------------------
*/
Route::prefix('sms')->group(function () {
    Route::post('/send', [SmsController::class, 'send']);
    Route::post('/send-direct', [SmsController::class, 'sendDirect']);
    Route::get('/log/{id}', [SmsController::class, 'getLog']);
    Route::get('/logs', [SmsController::class, 'getLogs']);
    Route::get('/stats', [SmsController::class, 'getStats']);
    Route::get('/provider', [SmsController::class, 'getProvider']);
    Route::post('/format-phone', [SmsController::class, 'formatPhone']);
});
