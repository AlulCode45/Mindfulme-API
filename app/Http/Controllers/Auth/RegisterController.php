<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\UserDetail;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $createUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'user',
        ]);
        if ($createUser) {
            UserDetail::create([
                'user_id' => $createUser->uuid,
                'phone' => $request->phone,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'bio' => $request->bio,
            ]);
            return ResponseHelper::success([
                'user' => $createUser,
                'token' => $createUser->createToken('auth_token')->plainTextToken,
            ], 'Registration successful. Please login.');
        } else {
            return ResponseHelper::error('Registration failed. Please try again.', 400);
        }
    }
}
