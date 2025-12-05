<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Testimonials;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        // Get users to associate testimonials with
        $users = User::where('uuid', '!=', '019ae548-e4cc-7292-a7ec-6fc6cf1def58')->get();

        if ($users->isEmpty()) {
            // If no users found, create some sample users
            $users = User::factory()->count(5)->create();
        }

        $testimonials = [
            [
                'title' => 'Layanan Konseling Sangat Membantu',
                'content' => 'Layanan konseling di MindfulMe sangat membantu saya mengatasi masalah kecemasan. Konselor sangat profesional dan mudah untuk diajak bicara. Terima kasih atas bantuannya.',
                'rating' => 5,
                'anonymous' => false,
                'approval_status' => 'approved',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(9),
            ],
            [
                'title' => 'Platform Mudah Digunakan',
                'content' => 'Saya sangat puas dengan layanan yang diberikan. Platform ini mudah digunakan dan konselor yang tersedia sangat berpengalaman. Recommended!',
                'rating' => 4,
                'anonymous' => false,
                'approval_status' => 'approved',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(7),
            ],
            [
                'title' => 'Konseling Keluarga Baik',
                'content' => 'Sesi konseling keluarga membantu kami memperbaiki komunikasi. Sangat direkomendasikan untuk keluarga yang mengalami masalah komunikasi.',
                'rating' => 5,
                'anonymous' => true,
                'approval_status' => 'approved',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(4),
            ],
            [
                'title' => 'Pelayanan Memuaskan',
                'content' => 'Konselor yang menangani saya sangat profesional dan sabar. Saya merasa lebih baik setelah beberapa sesi konseling bersama mereka.',
                'rating' => 4,
                'anonymous' => false,
                'approval_status' => 'pending',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'title' => 'Artikel Sangat Informatif',
                'content' => 'Artikel-artikel di website sangat informatif dan membantu saya memahami kondisi psikologis saya dengan lebih baik.',
                'rating' => 4,
                'anonymous' => false,
                'approval_status' => 'pending',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ];

        foreach ($testimonials as $testimonialData) {
            $user = $users->random();

            Testimonials::create(array_merge($testimonialData, [
                'user_id' => $user->uuid, // Use UUID instead of ID
                'user_name' => $testimonialData['anonymous'] ? 'Anonymous' : $user->name,
            ]));
        }
    }
}
