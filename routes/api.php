<?php

use App\Http\Controllers\Auth\ForgotPassword;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BundlePackage\UserBundlePointController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('forgot-password', [ForgotPassword::class, 'sendEmailResetPassword']);
Route::post('reset-password', [ForgotPassword::class, 'resetPassword']);

Route::post('/logout', \App\Http\Controllers\Auth\LogoutController::class)->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/midtrans/snap', [PaymentController::class, 'createSnapToken']);


    Route::apiResource('bundle-packages', \App\Http\Controllers\BundlePackage\BundlePackagesController::class);
    Route::prefix('/bundle-package-points')->group(function () {
        Route::get('/user/{uuid}', [UserBundlePointController::class, 'index']);
        Route::post('/purchase-point/{bundle_package_id}', [UserBundlePointController::class, 'store']);
    });


    Route::get('/user/complaints/{uuid}', [\App\Http\Controllers\ComplaintController::class, 'getComplaintByUserUuid']);
    Route::prefix('/complaints')->group(function () {
        Route::get('/', [\App\Http\Controllers\ComplaintController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\ComplaintController::class, 'storeComplaint']);
        Route::get('/{uuid}', [\App\Http\Controllers\ComplaintController::class, 'show']);
        Route::put('/{uuid}', [\App\Http\Controllers\ComplaintController::class, 'update']);
        Route::delete('/{uuid}', [\App\Http\Controllers\ComplaintController::class, 'destroy']);
    });

    Route::prefix('ai-discussion')->group(function () {
        Route::get('/', [\App\Http\Controllers\AIDiscussionController::class, 'get']);
        Route::get('/user/{uuid}', [\App\Http\Controllers\AIDiscussionController::class, 'getConversationsByUserUuid']);
        Route::get('/detail/{uuid}', [\App\Http\Controllers\AIDiscussionController::class, 'getConversationsByUuid']);
        Route::delete('/{uuid}', [\App\Http\Controllers\AIDiscussionController::class, 'deleteConversation']);
        Route::post('/save-conversation', [\App\Http\Controllers\AIDiscussionController::class, 'saveConversation']);
        Route::post('/send-to-complaint/{uuid}', [\App\Http\Controllers\AIDiscussionController::class, 'sendConversationToComplaint']);
    });
});
Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle']);
