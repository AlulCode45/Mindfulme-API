<?php

use App\Http\Controllers\Auth\OAuthController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('auth/google/redirect', [OAuthController::class, 'googleRedirect']);
Route::get('auth/google/callback', [OAuthController::class, 'googleCallback']);