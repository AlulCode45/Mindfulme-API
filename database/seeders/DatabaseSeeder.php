<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "Starting database seeding...\n";

        // Seed all data
        $this->call([
                // Users and basic data
            UsersSeeder::class,

                // News and content
            NewsCategorySeeder::class,
            ArticlesSeeder::class,
            SimpleArticleSeeder::class,

                // Session and appointment data
            SessionTypesSeeder::class,

                // Testimonials and reviews
            TestimonialsSeeder::class,

                // Complaints with chat
            ComplaintsSeeder::class,
        ]);

        echo "Database seeding completed!\n";
    }
}
