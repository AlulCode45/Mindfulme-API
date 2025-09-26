<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->tokens()->delete();
            return ResponseHelper::success([], 'Logout successful');
        }
        return ResponseHelper::error('No authenticated user', 401);
    }
}
