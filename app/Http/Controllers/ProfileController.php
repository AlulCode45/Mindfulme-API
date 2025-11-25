<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get user profile
     */
    public function getProfile(): JsonResponse
    {
        try {
            $user = auth()->user();

            // Load user with details
            $user->load('detail');

            return ResponseHelper::success($user, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth()->user();

        $baseRules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->uuid, 'uuid')
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'date_of_birth' => 'nullable|date|before:today',
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ];

        // Add psychologist-specific validation rules if user is psychologist
        if ($user->hasRole('psychologist')) {
            $baseRules = array_merge($baseRules, [
                'license_number' => 'nullable|string|max:50',
                'education' => 'nullable|string|max:1000',
                'specialization' => 'nullable|string|max:1000',
                'experience_years' => 'nullable|integer|min:0|max:50',
                'clinic_name' => 'nullable|string|max:255',
                'clinic_address' => 'nullable|string|max:1000',
                'consultation_fee' => 'nullable|numeric|min:0|max:999999.99'
            ]);
        }

        $validated = $request->validate($baseRules);

        try {
            DB::beginTransaction();

            // Update user basic info
            if (isset($validated['name']) || isset($validated['email'])) {
                $user->update([
                    'name' => $validated['name'] ?? $user->name,
                    'email' => $validated['email'] ?? $user->email,
                ]);
            }

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($user->detail && $user->detail->photo) {
                    Storage::disk('public')->delete($user->detail->photo);
                }

                $photoPath = $request->file('photo')->store('profile-photos', 'public');
            }

            // Update or create user details
            $userDetails = [
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ];

            // Add psychologist-specific fields if user is psychologist
            if ($user->hasRole('psychologist')) {
                $userDetails = array_merge($userDetails, [
                    'license_number' => $validated['license_number'] ?? null,
                    'education' => $validated['education'] ?? null,
                    'specialization' => $validated['specialization'] ?? null,
                    'experience_years' => $validated['experience_years'] ?? null,
                    'clinic_name' => $validated['clinic_name'] ?? null,
                    'clinic_address' => $validated['clinic_address'] ?? null,
                    'consultation_fee' => $validated['consultation_fee'] ?? null,
                ]);
            }

            if ($photoPath) {
                $userDetails['photo'] = $photoPath;
            }

            if ($user->detail) {
                $user->detail->update($userDetails);
            } else {
                $user->detail()->create($userDetails);
            }

            DB::commit();

            // Reload user with details
            $user->load('detail');

            return ResponseHelper::success($user, 'Profile updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error($e->getMessage(), 400);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = auth()->user();
            $user->update([
                'password' => bcrypt($validated['password'])
            ]);

            return ResponseHelper::success(null, 'Password changed successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 400);
        }
    }

    /**
     * Delete user account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|current_password',
        ]);

        try {
            $user = auth()->user();

            // Delete user's photo if exists
            if ($user->detail && $user->detail->photo) {
                Storage::disk('public')->delete($user->detail->photo);
            }

            // Delete user account (this will cascade delete user details)
            $user->delete();

            return ResponseHelper::success(null, 'Account deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 400);
        }
    }
}