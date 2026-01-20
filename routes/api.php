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

    // Consultation Types & Availability
    Route::get('/consultation-types', [ConsultationTypeController::class, 'index']);
    Route::get('/doctors/availability', [ConsultationTypeController::class, 'doctorAvailability']);

    // Appointments
    Route::get('/appointments/my', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::put('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);

    // Medical Records
    Route::get('/medical-records/my', [MedicalRecordController::class, 'index']);
    Route::get('/medical-records/{medicalRecord}', [MedicalRecordController::class, 'show']);
    Route::get('/prescriptions/my', [MedicalRecordController::class, 'prescriptions']);

    // Queues
    Route::get('/queues/active', [QueueController::class, 'active']);
    Route::get('/queues/my', [QueueController::class, 'myQueues']);
    Route::get('/queues/history', [QueueController::class, 'history']);
    Route::get('/queues/{queue}', [QueueController::class, 'show']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);

    // Devices (FCM Push Notifications)
    Route::post('/devices/register', [UserDeviceController::class, 'register']);
    Route::post('/devices/logout', [UserDeviceController::class, 'logout']);
    Route::put('/devices/token', [UserDeviceController::class, 'updateToken']);
    Route::get('/devices', [UserDeviceController::class, 'list']);
    Route::delete('/devices/{deviceId}', [UserDeviceController::class, 'destroy']);
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
