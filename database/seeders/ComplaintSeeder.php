<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if users exist, if not create them
        if (!DB::table('users')->where('email', 'budi.santoso@email.com')->exists()) {
            // Create sample users first
            $users = [
                [
                    'uuid' => '019adcc5-7c3f-7241-a1d4-68c4a0ebd5de',
                    'name' => 'Budi Santoso',
                    'email' => 'budi.santoso@email.com',
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'uuid' => '019adcc5-7c3f-7241-a1d4-68c4a0ebd5df',
                    'name' => 'Siti Nurhaliza',
                    'email' => 'siti.nurhaliza@email.com',
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'uuid' => '019adcc5-7c3f-7241-a1d4-68c4a0ebd5e0',
                    'name' => 'Ratna Sari Dewi',
                    'email' => 'ratna.sari@email.com',
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'uuid' => '019adcc5-7c3f-7241-a1d4-68c4a0ebd5e1',
                    'name' => 'Ahmad Fauzi',
                    'email' => 'ahmad.fauzi@email.com',
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('users')->insert($users);

            // Create user details with phone numbers
            $userDetails = [
                [
                    'user_detail_id' => 'ud-0000-0000-0000-000000000001',
                    'user_id' => '019adcc5-7c3f-7241-a1d4-68c4a0ebd5de',
                    'phone' => '+62812345678',
                    'address' => null,
                    'photo' => null,
                    'date_of_birth' => null,
                    'bio' => null,
                ],
                [
                    'user_detail_id' => 'ud-0000-0000-0000-000000000002',
                    'user_id' => '019adcc5-7c3f-7241-a1d4-68c4a0ebd5df',
                    'phone' => '+62823456789',
                    'address' => null,
                    'photo' => null,
                    'date_of_birth' => null,
                    'bio' => null,
                ],
                [
                    'user_detail_id' => 'ud-0000-0000-0000-000000000003',
                    'user_id' => '019adcc5-7c3f-7241-a1d4-68c4a0ebd5e0',
                    'phone' => '+62834567890',
                    'address' => null,
                    'photo' => null,
                    'date_of_birth' => null,
                    'bio' => null,
                ],
                [
                    'user_detail_id' => 'ud-0000-0000-0000-000000000004',
                    'user_id' => '019adcc5-7c3f-7241-a1d4-68c4a0ebd5e1',
                    'phone' => '+62845678901',
                    'address' => null,
                    'photo' => null,
                    'date_of_birth' => null,
                    'bio' => null,
                ],
            ];

            DB::table('user_details')->insert($userDetails);
        }

        // Create sample complaints
        $complaints = [
            [
                'complaint_id' => '0935ad9f-2851-4d74-8564-fbed41dcbadd',
                'ai_discussion_id' => null,
                'user_id' => '019adcc5-7c3f-7241-a1d4-68c4a0ebd5de',
                'title' => 'Masalah Finansial dan Hutang',
                'description' => 'Mengalami masalah finansial yang berat dengan hutang menumpuk',
                'chronology' => 'Pengguna memulai percakapan dengan niat untuk bercerita tentang masalah finansial yang sedang dihadapi. Pengguna memiliki beberapa hutang baik dari pinjaman online maupun dari teman/keluarga. Pengguna merasa tertekan dan bingung bagaimana cara mengatasinya. Pengguna ingin mendapatkan saran tentang cara mengelola keuangan dan menyelesaikan masalah hutang.',
                'category' => 'Kesehatan Mental',
                'status' => 'new',
                'priority' => 'normal',
                'classification' => 'psikologi',
                'admin_notes' => 'Perlu sesi konseling untuk manajemen stres terkait finansial',
                'scheduled_date' => '2025-12-05',
                'scheduled_time' => '14:00:00',
                'assigned_to' => null,
                'response_count' => 0,
                'chat_history' => null,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDay(),
            ],
            [
                'complaint_id' => '0935ad9f-2851-4d74-8564-fbed41dcbaee',
                'ai_discussion_id' => null,
                'user_id' => '019adcc5-7c3f-7241-a1d4-68c4a0ebd5df',
                'title' => 'Bully di Lingkungan Sekolah',
                'description' => 'Anak mengalami bullying verbal dan fisik di sekolah',
                'chronology' => 'Kejadian berulang selama 2 bulan terakhir dengan berbagai bentuk bullying. Korban mengalami trauma dan ketakutan berlebih.',
                'category' => 'Kekerasan',
                'status' => 'new',
                'priority' => 'urgent',
                'classification' => null,
                'admin_notes' => null,
                'scheduled_date' => null,
                'scheduled_time' => null,
                'assigned_to' => null,
                'response_count' => 0,
                'chat_history' => null,
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
            [
                'complaint_id' => '0935ad9f-2851-4d74-8564-fbed41dcbaff',
                'ai_discussion_id' => null,
                'user_id' => '019adcc5-7c3f-7241-a1d4-68c4a0ebd5e0',
                'title' => 'Depresi Pasca Melahirkan',
                'description' => 'Mengalami baby blues yang berlanjut menjadi depresi',
                'chronology' => 'Gejala muncul 2 bulan setelah melahirkan dengan kesulitan merawat bayi dan perubahan emosi yang drastis.',
                'category' => 'Kesehatan Mental',
                'status' => 'completed',
                'priority' => 'normal',
                'classification' => 'psikologi',
                'admin_notes' => 'Selesai konseling 6 sesi, kondisi membaik',
                'scheduled_date' => '2025-11-15',
                'scheduled_time' => '10:00:00',
                'assigned_to' => null,
                'response_count' => 8,
                'chat_history' => null,
                'created_at' => now()->subDays(14),
                'updated_at' => now()->subDays(2),
            ],
            [
                'complaint_id' => '0935ad9f-2851-4d74-8564-fbed41dcbagg',
                'ai_discussion_id' => null,
                'user_id' => '019adcc5-7c3f-7241-a1d4-68c4a0ebd5e1',
                'title' => 'Konflik Keluarga',
                'description' => 'Masalah komunikasi antar anggota keluarga yang berujung pertengkaran',
                'chronology' => 'Konflik sudah terjadi selama 1 tahun dengan frekuensi yang meningkat akhir-akhir ini. Ada masalah komunikasi dan pemahaman antar anggota keluarga.',
                'category' => 'Hubungan',
                'status' => 'new',
                'priority' => 'normal',
                'classification' => null,
                'admin_notes' => null,
                'scheduled_date' => null,
                'scheduled_time' => null,
                'assigned_to' => null,
                'response_count' => 0,
                'chat_history' => null,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
        ];

        DB::table('complaints')->insert($complaints);
    }
}
