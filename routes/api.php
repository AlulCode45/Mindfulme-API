<?php

use App\Http\Controllers\Auth\ForgotPassword;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('forgot-password', [ForgotPassword::class, 'sendEmailResetPassword']);
Route::post('reset-password', [ForgotPassword::class, 'resetPassword']);

Route::post('/logout', \App\Http\Controllers\Auth\LogoutController::class)->middleware('auth:sanctum');