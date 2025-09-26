<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Str;

class ForgotPassword extends Controller
{
    public function sendEmailResetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::ResetLinkSent) {
            return ResponseHelper::success([
                'status' => $status,
            ], __($status) . ' Please check your email for the password reset link.');
        } else {
            return ResponseHelper::error('Failed to send password reset link.', 400);
        }
    }

    public function resetPassword(Request $request, $token)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PasswordReset) {
            return ResponseHelper::success([], 'Password reset successful. You can now log in with your new password.');
        } else {
            return ResponseHelper::error(__($status), 400);
        }
    }
}
