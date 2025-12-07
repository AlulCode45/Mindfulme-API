<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superadmin = User::firstOrCreate([
            'email' => 'admin@mail.com'
        ], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);

        // Create User Details for superadmin
        if (!$superadmin->userDetail) {
            UserDetail::create([
                'user_id' => $superadmin->uuid,
                'phone' => '08123456789',
                'address' => 'Jakarta, Indonesia',
                'bio' => 'System administrator for MindfulMe platform',
            ]);
        }

        echo "Created superadmin user: " . $superadmin->email . "\n";

        // Create regular users
        $users = [
            ['name' => 'John Doe', 'email' => 'user@mail.com', 'role' => 'user'],
            ['name' => 'Jane Smith', 'email' => 'jane.smith@example.com', 'role' => 'user'],
            ['name' => 'Alice Johnson', 'email' => 'alice.j@example.com', 'role' => 'user'],
            ['name' => 'Bob Wilson', 'email' => 'bob.w@example.com', 'role' => 'user'],
            ['name' => 'Emma Davis', 'email' => 'emma.d@example.com', 'role' => 'user'],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate([
                'email' => $userData['email']
            ], [
                'name' => $userData['name'],
                'password' => Hash::make('password'),
                'role' => $userData['role'],
                'email_verified_at' => now(),
            ]);

            // Create user details
            if (!$user->userDetail) {
                UserDetail::create([
                    'user_id' => $user->uuid,
                    'phone' => '08' . rand(100000000, 999999999),
                    'address' => $this->getRandomAddress(),
                    'bio' => $this->getRandomBio(),
                    'date_of_birth' => now()->subYears(rand(18, 45))->subDays(rand(0, 365)),
                ]);
            }

            echo "Created user: " . $user->email . "\n";
        }

        // Create volunteers
        $volunteers = [
            ['name' => 'Volunteer One', 'email' => 'volunteer1@example.com'],
            ['name' => 'Volunteer Two', 'email' => 'volunteer2@example.com'],
            ['name' => 'Volunteer Three', 'email' => 'volunteer3@example.com'],
            ['name' => 'Volunteer Four', 'email' => 'volunteer4@example.com'],
            ['name' => 'Volunteer Five', 'email' => 'volunteer5@example.com'],
        ];

        foreach ($volunteers as $volunteerData) {
            $volunteer = User::firstOrCreate([
                'email' => $volunteerData['email']
            ], [
                'name' => $volunteerData['name'],
                'password' => Hash::make('password'),
                'role' => 'volunteer',
                'volunteer_status' => rand(0, 1) ? 'approved' : 'pending',
                'motivation' => $this->getRandomMotivation(),
                'email_verified_at' => now(),
            ]);

            // Create user details for volunteers
            if (!$volunteer->userDetail) {
                UserDetail::create([
                    'user_id' => $volunteer->uuid,
                    'phone' => '08' . rand(100000000, 999999999),
                    'address' => $this->getRandomAddress(),
                    'bio' => $this->getRandomBio(),
                    'date_of_birth' => now()->subYears(rand(20, 50))->subDays(rand(0, 365)),
                ]);
            }

            echo "Created volunteer: " . $volunteer->email . " (Status: " . $volunteer->volunteer_status . ")\n";
        }

        // Create additional regular users (instead of psychologists due to role constraint)
        $additionalUsers = [
            ['name' => 'Sarah Chen', 'email' => 'sarah.chen@example.com'],
            ['name' => 'Ahmad Faisal', 'email' => 'ahmad.faisal@example.com'],
            ['name' => 'Maya Putri', 'email' => 'maya.putri@example.com'],
            ['name' => 'Rizky Pratama', 'email' => 'rizky.p@example.com'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti.n@example.com'],
        ];

        foreach ($additionalUsers as $userData) {
            $user = User::firstOrCreate([
                'email' => $userData['email']
            ], [
                'name' => $userData['name'],
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);

            // Create user details for additional users
            if (!$user->userDetail) {
                UserDetail::create([
                    'user_id' => $user->uuid,
                    'phone' => '08' . rand(100000000, 999999999),
                    'address' => $this->getRandomAddress(),
                    'bio' => $this->getRandomBio(),
                    'date_of_birth' => now()->subYears(rand(18, 45))->subDays(rand(0, 365)),
                ]);
            }

            echo "Created user: " . $user->email . "\n";
        }

        echo "UsersSeeder completed successfully!\n";
    }

    private function getRandomAddress(): string
    {
        $addresses = [
            'Jl. Sudirman No. 123, Jakarta Pusat',
            'Jl. Thamrin No. 456, Jakarta Selatan',
            'Jl. Gatot Subroto No. 789, Jakarta Barat',
            'Jl. Rasuna Said No. 321, Jakarta Selatan',
            'Jl. MH Thamrin No. 654, Jakarta Pusat',
        ];
        return $addresses[array_rand($addresses)];
    }

    private function getRandomBio(): string
    {
        $bios = [
            'Passionate about mental health and personal growth',
            'Interested in psychology and helping others',
            'Love learning new things and connecting with people',
            'Believe in the power of self-improvement',
            'Enjoy outdoor activities and mindfulness practices',
        ];
        return $bios[array_rand($bios)];
    }

    private function getRandomMotivation(): string
    {
        $motivations = [
            'I want to help people improve their mental well-being',
            'I believe everyone deserves access to mental health support',
            'I want to learn more about psychology and counseling',
            'I have personal experience with mental health challenges',
            'I want to make a positive impact in my community',
            'I believe in the power of human connection and support',
            'I want to contribute to mental health awareness',
            'I am passionate about helping others overcome their struggles',
        ];
        return $motivations[array_rand($motivations)];
    }

    private function getPsychologistBio(): string
    {
        $bios = [
            'Licensed clinical psychologist with 10+ years of experience in cognitive behavioral therapy',
            'Specialized in adolescent psychology and family counseling',
            'Expert in anxiety disorders and stress management techniques',
            'Passionate about helping young adults navigate life transitions',
            'Certified trauma-informed therapist specializing in PTSD and recovery',
            'Experienced in couples therapy and relationship counseling',
            'Specialized in child psychology and developmental disorders',
            'Expert in depression treatment and mood disorders',
        ];
        return $bios[array_rand($bios)];
    }
}