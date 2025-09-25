<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $createUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        if ($createUser) {
            return redirect('/login')->with('success', 'Registration successful. Please login.');
        } else {
            return back()->withErrors(['registration' => 'Registration failed. Please try again.'])->withInput();
        }
    }
}
