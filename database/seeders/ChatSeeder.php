<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a test user
        $user = DB::table('users')->where('email', 'testuser@example.com')->first();
        if (!$user) {
            $userId = (string) Str::uuid();
            DB::table('users')->insert([
                'uuid' => $userId,
                'name' => 'Test User',
                'email' => 'testuser@example.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $userId = $user->uuid;
        }

        // Get or create a test admin
        $admin = DB::table('users')->where('email', 'admin@example.com')->first();
        if (!$admin) {
            $adminId = (string) Str::uuid();
            DB::table('users')->insert([
                'uuid' => $adminId,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $adminId = $admin->uuid;
        }

        // Insert sample complaints with proper IDs
        $complaints = [
            [
                'complaint_id' => '550e8400-e29b-41d4-a716-446655440001',
                'user_id' => $userId,
                'title' => 'Keluhan tentang kecemasan sosial',
                'description' => 'Saya merasa cemas berinteraksi dengan orang baru',
                'chronology' => 'Mulai terasa 3 bulan terakhir',
                'category' => 'mental',
                'status' => 'classified',
                'classification' => 'psikologi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'complaint_id' => '550e8400-e29b-41d4-a716-446655440002',
                'user_id' => $userId,
                'title' => 'Masalah konsentrasi dalam belajar',
                'description' => 'Sulit fokus saat mengerjakan tugas',
                'chronology' => 'Dialami sejak pandemi',
                'category' => 'mental',
                'status' => 'classified',
                'classification' => 'psikologi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'complaint_id' => '550e8400-e29b-41d4-a716-446655440003',
                'user_id' => $userId,
                'title' => 'Stress pekerjaan',
                'description' => 'Tekanan dari deadline pekerjaan yang terlalu banyak',
                'chronology' => 'Semakin parah dalam 2 bulan terakhir',
                'category' => 'work',
                'status' => 'classified',
                'classification' => 'konseling',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert complaints if they don't exist
        foreach ($complaints as $complaint) {
            $exists = DB::table('complaints')->where('complaint_id', $complaint['complaint_id'])->first();
            if (!$exists) {
                DB::table('complaints')->insert($complaint);
            }
        }

        // Insert sample chat messages
        $messages = [
            [
                'complaint_id' => '550e8400-e29b-41d4-a716-446655440001',
                'sender_id' => $userId,
                'sender_type' => 'user',
                'message_text' => 'Halo admin, saya ingin berbicara tentang masalah kecemasan saya',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ],
            [
                'complaint_id' => '550e8400-e29b-41d4-a716-446655440001',
                'sender_id' => $adminId,
                'sender_type' => 'admin',
                'message_text' => 'Halo! Terima kasih telah menghubungi kami. Saya siap membantu Anda. Bisa ceritakan lebih detail tentang kecemasan yang Anda alami?',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subMinutes(25),
                'updated_at' => now()->subMinutes(25),
            ],
            [
                'complaint_id' => '550e8400-e29b-41d4-a716-446655440001',
                'sender_id' => $userId,
                'sender_type' => 'user',
                'message_text' => 'Saya merasa nervous ketika harus presentasi di depan banyak orang',
                'message_type' => 'text',
                'is_read' => false,
                'created_at' => now()->subMinutes(20),
                'updated_at' => now()->subMinutes(20),
            ],
            [
                'complaint_id' => '550e8400-e29b-41d4-a716-446655440002',
                'sender_id' => $userId,
                'sender_type' => 'user',
                'message_text' => 'Admin, saya punya masalah dengan konsentrasi',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'complaint_id' => '550e8400-e29b-41d4-a716-446655440002',
                'sender_id' => $adminId,
                'sender_type' => 'admin',
                'message_text' => 'Baik, mari kita diskusikan. Sudah berapa lama Anda mengalami ini?',
                'message_type' => 'text',
                'is_read' => true,
                'created_at' => now()->subHours(1, 50),
                'updated_at' => now()->subHours(1, 50),
            ],
        ];

        // Insert messages if they don't exist
        foreach ($messages as $message) {
            $exists = DB::table('chats')
                ->where('complaint_id', $message['complaint_id'])
                ->where('sender_id', $message['sender_id'])
                ->where('created_at', $message['created_at'])
                ->first();

            if (!$exists) {
                DB::table('chats')->insert($message);
            }
        }

        $this->command->info('Sample chat data seeded successfully!');
    }
}