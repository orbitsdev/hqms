<?php

use App\Http\Controllers\Api\AuthController;
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
