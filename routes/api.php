<?php

use App\Http\Controllers\Auth\ForgotPassword;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BundlePackage\UserBundlePointController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Session\PsychologistAvailabilityController;
use App\Http\Controllers\Session\SessionController;
use App\Http\Controllers\Session\SessionTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Psychologist\PsychologistAnalyticsController;
use App\Http\Controllers\Psychologist\PatientManagementController;
use App\Http\Controllers\PsychologistController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('forgot-password', [ForgotPassword::class, 'sendEmailResetPassword']);
Route::post('reset-password', [ForgotPassword::class, 'resetPassword']);

Route::post('/logout', \App\Http\Controllers\Auth\LogoutController::class)->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json(['data' => auth()->user()]);
});

// Public routes
Route::prefix('psychologists')->group(function () {
    Route::get('/', [PsychologistController::class, 'index']);
    Route::get('/{uuid}', [PsychologistController::class, 'show']);
});

// Public session-types routes
Route::prefix('session-types')->group(function () {
    Route::get('/', [SessionTypeController::class, 'index']);
    Route::get('/{id}', [SessionTypeController::class, 'show']);
    Route::get('/consultation-type/{type}', [SessionTypeController::class, 'getByConsultationType']);
});

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

    // Session Scheduling Routes (Protected - admin only)
    Route::prefix('session-types')->group(function () {
        // Admin only routes
        Route::post('/', [SessionTypeController::class, 'store']);
        Route::put('/{id}', [SessionTypeController::class, 'update']);
        Route::delete('/{id}', [SessionTypeController::class, 'destroy']);
    });

    Route::prefix('psychologist-availability')->group(function () {
        Route::get('/available-slots', [PsychologistAvailabilityController::class, 'getAvailableTimeSlots']);
        Route::get('/check-availability', [PsychologistAvailabilityController::class, 'checkTimeSlotAvailability']);
        Route::get('/', [PsychologistAvailabilityController::class, 'index']);
        Route::get('/my', [PsychologistAvailabilityController::class, 'getMyAvailability']);
        Route::get('/{id}', [PsychologistAvailabilityController::class, 'show']);
        Route::post('/', [PsychologistAvailabilityController::class, 'store']);
        Route::put('/{id}', [PsychologistAvailabilityController::class, 'update']);
        Route::delete('/{id}', [PsychologistAvailabilityController::class, 'destroy']);
    });

    Route::prefix('sessions')->group(function () {
        Route::get('/types', [SessionController::class, 'getSessionTypes']);
        Route::post('/book', [SessionController::class, 'bookSession']);
        Route::get('/my', [SessionController::class, 'getMySessions']);
        Route::get('/upcoming', [SessionController::class, 'getUpcomingSessions']);
        Route::get('/psychologist', [SessionController::class, 'getPsychologistSessions']);
        Route::get('/{id}', [SessionController::class, 'getSessionDetails']);
        Route::put('/{id}/status', [SessionController::class, 'updateSessionStatus']);
        Route::post('/{id}/reschedule', [SessionController::class, 'rescheduleSession']);
    });

    // Profile Management Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'getProfile']);
        Route::put('/', [ProfileController::class, 'updateProfile']);
        Route::post('/change-password', [ProfileController::class, 'changePassword']);
        Route::delete('/', [ProfileController::class, 'deleteAccount']);
    });

    // Admin Analytics Routes
    Route::prefix('admin/analytics')->group(function () {
        Route::get('/dashboard-stats', [AdminAnalyticsController::class, 'getDashboardStats']);
        Route::get('/recent-activity', [AdminAnalyticsController::class, 'getRecentActivity']);
        Route::get('/user-growth', [AdminAnalyticsController::class, 'getUserGrowthData']);
        Route::get('/session-stats', [AdminAnalyticsController::class, 'getSessionStatsData']);
    });

    // Psychologist Analytics Routes
    Route::prefix('psychologist/analytics')->group(function () {
        Route::get('/stats', [PsychologistAnalyticsController::class, 'getStats']);
        Route::get('/sessions', [PsychologistAnalyticsController::class, 'getSessionAnalytics']);
        Route::get('/patients', [PsychologistAnalyticsController::class, 'getPatients']);
    });

    // Psychologist Patient Management Routes
    Route::prefix('psychologist/patients')->group(function () {
        Route::get('/', [PatientManagementController::class, 'index']);
        Route::get('/{userId}', [PatientManagementController::class, 'show']);
        Route::get('/{userId}/sessions', [PatientManagementController::class, 'getSessionHistory']);
        Route::post('/sessions/{sessionId}/notes', [PatientManagementController::class, 'addSessionNotes']);
    });

    // Review Management Routes
    Route::prefix('reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'index']);
        Route::post('/', [ReviewController::class, 'store']);
        Route::get('/user', [ReviewController::class, 'getUserReviews']);
        Route::get('/{reviewId}', [ReviewController::class, 'show']);
        Route::put('/{reviewId}', [ReviewController::class, 'update']);
        Route::delete('/{reviewId}', [ReviewController::class, 'destroy']);
        Route::get('/psychologist/{psychologistId}/stats', [ReviewController::class, 'getPsychologistStats']);
    });

    // Chat Routes
    Route::prefix('chat')->group(function () {
        Route::get('/messages', [ChatController::class, 'getMessages']);
        Route::get('/messages/{sessionId}', [ChatController::class, 'getMessages']);
        Route::post('/send', [ChatController::class, 'sendMessage']);
        Route::get('/partners', [ChatController::class, 'getChatPartners']);
        Route::get('/sessions', [ChatController::class, 'getChatSessions']);
        Route::post('/mark-read', [ChatController::class, 'markAsRead']);
        Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);
        Route::put('/messages/{messageId}', [ChatController::class, 'updateMessage']);
        Route::delete('/messages/{messageId}', [ChatController::class, 'deleteMessage']);
    });
});
Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle']);
