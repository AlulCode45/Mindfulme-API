<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (\App\Enums\Roles::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value]);
        }

        $user = User::create([
            'name' => 'User',
            'email' => 'user@mail.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole(\App\Enums\Roles::USER->value);

        // Super Admin
        $superadmin = User::create([
            'name' => 'Admin',
            'email' => 'admin@mail.com',
            'password' => bcrypt('password'),
        ]);
        $superadmin->assignRole(\App\Enums\Roles::SUPERADMIN->value);

        // Psychologist
        $psychologist = User::create([
            'name' => 'Psychologist',
            'email' => 'psikolog@mail.com',
            'password' => bcrypt('password'),
        ]);
        $psychologist->assignRole(\App\Enums\Roles::PSYCHOLOGIST->value);

        // Seed session types and complaints
        $this->call([
            SessionTypesSeeder::class,
            ComplaintSeeder::class,
        ]);

    }
}
