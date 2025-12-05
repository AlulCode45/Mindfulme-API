<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:superadmin');
    }

    /**
     * Get all users with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with('detail', 'roles');

            // Search by name or email
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($request->has('role')) {
                $role = $request->input('role');
                $query->role($role);
            }

    
            // Sort by
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->input('per_page', 10);
            $users = $query->paginate($perPage);

            return ResponseHelper::success($users, 'Users retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get specific user by UUID
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $user = User::with('detail', 'roles')->where('uuid', $uuid)->first();

            if (!$user) {
                return ResponseHelper::error('User not found', 404);
            }

            return ResponseHelper::success($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Create new user
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'date_of_birth' => 'nullable|date|before:today',
            'bio' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(), // Auto-verify for admin-created users
            ]);

            // Assign role
            $user->assignRole($validated['role']);

            // Create user details if provided
            $userDetails = [
                'user_id' => $user->uuid,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ];

    
            UserDetail::create($userDetails);

            DB::commit();

            // Load user with details and roles
            $user->load('detail', 'roles');

            return ResponseHelper::success($user, 'User created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error($e->getMessage(), 400);
        }
    }

    /**
     * Update existing user
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $user = User::where('uuid', $uuid)->first();

        if (!$user) {
            return ResponseHelper::error('User not found', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->uuid, 'uuid')
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'sometimes|required|exists:roles,name',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'date_of_birth' => 'nullable|date|before:today',
            'bio' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Update user basic info
            $userData = [];
            if (isset($validated['name'])) $userData['name'] = $validated['name'];
            if (isset($validated['email'])) $userData['email'] = $validated['email'];
            if (isset($validated['password']) && !empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            if (!empty($userData)) {
                $user->update($userData);
            }

            // Update role if provided
            if (isset($validated['role'])) {
                $user->syncRoles([$validated['role']]);
            }

            // Update or create user details
            $userDetails = [
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ];

  
            if ($user->detail) {
                $user->detail->update($userDetails);
            } else {
                $userDetails['user_id'] = $user->uuid;
                UserDetail::create($userDetails);
            }

            DB::commit();

            // Load user with details and roles
            $user->load('detail', 'roles');

            return ResponseHelper::success($user, 'User updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error($e->getMessage(), 400);
        }
    }

    /**
     * Delete user
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $user = User::where('uuid', $uuid)->first();

            if (!$user) {
                return ResponseHelper::error('User not found', 404);
            }

            // Prevent deletion of self
            if ($user->uuid === auth()->user()->uuid) {
                return ResponseHelper::error('Cannot delete your own account', 400);
            }

            // Delete user's photo if exists
            if ($user->detail && $user->detail->photo) {
                Storage::disk('public')->delete($user->detail->photo);
            }

            // Delete user (this will cascade delete user details due to foreign key)
            $user->delete();

            return ResponseHelper::success(null, 'User deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 400);
        }
    }

  
    /**
     * Get available roles
     */
    public function getRoles(): JsonResponse
    {
        try {
            $roles = Role::select('name as value', 'name as label')->get();
            return ResponseHelper::success($roles, 'Roles retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get user statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'superadmin_users' => User::role('superadmin')->count(),
                'regular_users' => User::role('user')->count(),
                'users_this_month' => User::whereMonth('created_at', now()->month)->count(),
                'users_this_year' => User::whereYear('created_at', now()->year)->count(),
            ];

            return ResponseHelper::success($stats, 'User statistics retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}