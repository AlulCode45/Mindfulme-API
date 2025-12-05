<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['superadmin', 'user']; // Simple system: only admin and user roles
        $guards = ['api', 'web'];

        foreach ($guards as $guard) {
            foreach ($roles as $roleName) {
                // Check if role already exists
                if (!Role::where('name', $roleName)->where('guard_name', $guard)->exists()) {
                    Role::create([
                        'id' => Str::uuid(),
                        'name' => $roleName,
                        'guard_name' => $guard
                    ]);
                }
            }
        }
    }
}