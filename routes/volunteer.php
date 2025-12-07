<?php

use App\Http\Controllers\Api\VolunteerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Volunteer API Routes
|--------------------------------------------------------------------------
|
| Routes for volunteer registration, authentication, and management
|
*/

// Public volunteer routes
Route::prefix('volunteers')->group(function () {
    Route::post('/register', [VolunteerController::class, 'register']);
    Route::post('/login', [VolunteerController::class, 'login']);
});

// Protected volunteer routes
Route::middleware('auth:sanctum')->prefix('volunteers')->group(function () {
    Route::get('/profile', [VolunteerController::class, 'profile']);
    Route::post('/logout', [VolunteerController::class, 'logout']);

    // Admin only routes
    Route::group([], function () {
        Route::get('/all', [VolunteerController::class, 'getAllVolunteers']);
        Route::get('/pending', [VolunteerController::class, 'pendingVolunteers']);
        Route::post('/{uuid}/approve', [VolunteerController::class, 'approveVolunteer']);
        Route::post('/{uuid}/reject', [VolunteerController::class, 'rejectVolunteer']);
    });
});