<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VolunteerController extends Controller
{
    /**
     * Register a new volunteer
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'motivation' => 'required|string|min:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $volunteer = User::create([
                'uuid' => Str::uuid()->toString(),
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => 'volunteer',
                'address' => $request->address,
                'motivation' => $request->motivation,
                'volunteer_status' => 'pending',
                'volunteer_notes' => 'Submitted for admin review',
            ]);

            // Create token for immediate login
            $token = $volunteer->createToken('volunteer-token')->plainTextToken;

            return response()->json([
                'message' => 'Volunteer registration successful',
                'data' => [
                    'user' => [
                        'uuid' => $volunteer->uuid,
                        'name' => $volunteer->name,
                        'email' => $volunteer->email,
                        'role' => $volunteer->role,
                        'volunteer_status' => $volunteer->volunteer_status,
                    ],
                    'token' => $token,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login volunteer
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $volunteer = User::where('email', $request->email)
            ->where('role', 'volunteer')
            ->first();

        if (!$volunteer || !Hash::check($request->password, $volunteer->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if ($volunteer->volunteer_status !== 'approved') {
            return response()->json([
                'message' => 'Your volunteer application is still pending review'
            ], 403);
        }

        // Revoke existing tokens
        $volunteer->tokens()->delete();

        // Create new token
        $token = $volunteer->createToken('volunteer-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'uuid' => $volunteer->uuid,
                    'name' => $volunteer->name,
                    'email' => $volunteer->email,
                    'role' => $volunteer->role,
                    'volunteer_status' => $volunteer->volunteer_status,
                ],
                'token' => $token,
            ]
        ]);
    }

    /**
     * Get volunteer profile
     */
    public function profile(Request $request)
    {
        $volunteer = $request->user();

        if (!$volunteer->isVolunteer()) {
            return response()->json([
                'message' => 'Unauthorized - not a volunteer'
            ], 403);
        }

        return response()->json([
            'message' => 'Profile retrieved successfully',
            'data' => [
                'uuid' => $volunteer->uuid,
                'name' => $volunteer->name,
                'email' => $volunteer->email,
                'phone' => $volunteer->phone,
                'address' => $volunteer->address,
                'motivation' => $volunteer->motivation,
                'role' => $volunteer->role,
                'volunteer_status' => $volunteer->volunteer_status,
                'volunteer_notes' => $volunteer->volunteer_notes,
                'created_at' => $volunteer->created_at,
            ]
        ]);
    }

    /**
     * Logout volunteer
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get all volunteers (for admin)
     */
    public function getAllVolunteers(Request $request)
    {
        $volunteers = User::volunteers()
            ->orderBy('created_at', 'desc')
            ->get(['uuid', 'name', 'email', 'phone', 'address', 'motivation', 'volunteer_status', 'volunteer_notes', 'created_at', 'updated_at']);

        return response()->json([
            'message' => 'All volunteers retrieved successfully',
            'data' => $volunteers
        ]);
    }

    /**
     * Get all pending volunteers (for admin)
     */
    public function pendingVolunteers(Request $request)
    {
        $volunteers = User::pendingVolunteers()
            ->orderBy('created_at', 'desc')
            ->get(['uuid', 'name', 'email', 'phone', 'address', 'motivation', 'created_at']);

        return response()->json([
            'message' => 'Pending volunteers retrieved successfully',
            'data' => $volunteers
        ]);
    }

    /**
     * Approve volunteer application (for admin)
     */
    public function approveVolunteer(Request $request, $uuid)
    {
        $volunteer = User::where('uuid', $uuid)
            ->where('role', 'volunteer')
            ->where('volunteer_status', 'pending')
            ->first();

        if (!$volunteer) {
            return response()->json([
                'message' => 'Volunteer not found or already processed'
            ], 404);
        }

        $volunteer->update([
            'volunteer_status' => 'approved',
            'volunteer_notes' => $request->notes ?? 'Application approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->uuid,
        ]);

        return response()->json([
            'message' => 'Volunteer approved successfully',
            'data' => $volunteer
        ]);
    }

    /**
     * Reject volunteer application (for admin)
     */
    public function rejectVolunteer(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $volunteer = User::where('uuid', $uuid)
            ->where('role', 'volunteer')
            ->where('volunteer_status', 'pending')
            ->first();

        if (!$volunteer) {
            return response()->json([
                'message' => 'Volunteer not found or already processed'
            ], 404);
        }

        $volunteer->update([
            'volunteer_status' => 'rejected',
            'volunteer_notes' => $request->notes,
            'rejected_at' => now(),
            'rejected_by' => $request->user()->uuid,
        ]);

        return response()->json([
            'message' => 'Volunteer application rejected',
            'data' => $volunteer
        ]);
    }
}