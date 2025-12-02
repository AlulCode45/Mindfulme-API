<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['superadmin', 'user', 'psychologist', 'admin'];

        foreach ($roles as $roleName) {
            Role::create([
                'id' => Str::uuid(),
                'name' => $roleName,
                'guard_name' => 'api'
            ]);
        }
    }
}