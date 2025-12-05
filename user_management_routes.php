<?php

// User Management Routes - include this in api.php

use App\Http\Controllers\Admin\UserManagementController;

// Admin User Management Routes
Route::prefix('admin/users')->middleware(['role:superadmin'])->group(function () {
    Route::get('/', [UserManagementController::class, 'index']);
    Route::get('/stats', [UserManagementController::class, 'getStats']);
    Route::get('/roles', [UserManagementController::class, 'getRoles']);
    Route::get('/{uuid}', [UserManagementController::class, 'show']);
    Route::post('/', [UserManagementController::class, 'store']);
    Route::put('/{uuid}', [UserManagementController::class, 'update']);
    Route::delete('/{uuid}', [UserManagementController::class, 'destroy']);
});