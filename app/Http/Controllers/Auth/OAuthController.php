<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    public function googleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }
    public function googleCallback()
    {
        try {
            // Ambil data user dari Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Cari user di database
            $user = User::where('email', $googleUser->getEmail())->first();

            // Kalau belum ada, buat user baru
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(str()->random(16)), // random password
                ]);
            }

            // Generate Sanctum token untuk API
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login with Google successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google login failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
