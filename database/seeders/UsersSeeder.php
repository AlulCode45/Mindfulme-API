<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create psychologist user
        $psychologist = User::firstOrCreate([
            'email' => 'psikolog@mail.com'
        ], [
            'name' => 'Psychologist',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        if (!$psychologist->hasRole('psychologist')) {
            $psychologist->assignRole('psychologist');
        }

        echo "Created psychologist user: " . $psychologist->email . "\n";
    }
}