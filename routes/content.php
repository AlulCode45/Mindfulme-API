<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\ContentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Content Management API Routes
|--------------------------------------------------------------------------
|
| Routes for articles, videos, categories, and content management
|
*/

// Public content routes (no authentication required)
Route::prefix('content')->group(function () {
    Route::get('/categories', [ContentController::class, 'categories']);
    Route::get('/tags', [ContentController::class, 'tags']);
    Route::get('/stats', [ContentController::class, 'stats']);
    Route::get('/search', [ContentController::class, 'search']);
});

// Articles routes
Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::get('/slug/{slug}', [ArticleController::class, 'getBySlug']);

    // Public volunteer submission route (protected by sanctum)
    Route::post('/volunteer-submit', [ArticleController::class, 'volunteerSubmit'])
        ->middleware('auth:sanctum');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [ArticleController::class, 'store']);

        // Verification workflow routes (must come before parameterized routes)
        Route::get('/pending-verification', [ArticleController::class, 'pendingVerification']);

        // Volunteer specific routes
        Route::get('/volunteer/my-articles', [ArticleController::class, 'getVolunteerArticles'])
            ->middleware('auth:sanctum');

        Route::get('/{article}', [ArticleController::class, 'show']);
        Route::put('/{article}', [ArticleController::class, 'update']);
        Route::delete('/{article}', [ArticleController::class, 'destroy']);
        Route::post('/{article}/view', [ArticleController::class, 'trackView']);
        Route::post('/{article}/approve', [ArticleController::class, 'approve']);
        Route::post('/{article}/reject', [ArticleController::class, 'reject']);
    });
});

// Videos routes
Route::prefix('videos')->group(function () {
    Route::get('/', [VideoController::class, 'index']);
    Route::get('/slug/{slug}', [VideoController::class, 'getBySlug']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [VideoController::class, 'store']);
        Route::get('/{video}', [VideoController::class, 'show']);
        Route::put('/{video}', [VideoController::class, 'update']);
        Route::delete('/{video}', [VideoController::class, 'destroy']);
        Route::post('/{video}/view', [VideoController::class, 'trackView']);
    });
});