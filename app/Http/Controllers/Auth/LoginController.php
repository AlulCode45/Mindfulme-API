<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Hash;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user and Hash::check($request->password, $user->password)) {

            return ResponseHelper::success([
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
                'role' => $user->getRoleNames()->first(),
            ], 'Login successful');
        }

        return ResponseHelper::error('Login failed', 401);
    }
}
