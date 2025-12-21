<?php

namespace Database\Seeders;

use App\Enums\ComplaintStatus;
use App\Models\Complaints;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplaintsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample users for testing
        $users = User::limit(3)->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UsersSeeder first.');
            return;
        }

        // Get an admin user (assuming role_id = 1 is admin)
        $admin = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        if (!$admin) {
            // Fallback to first user
            $admin = $users->first();
        }

        // Sample complaints data
        $complaintsData = [
            [
                'user_id' => $users[0]->uuid,
                'title' => 'Keluhan Kecemasan Sosial',
                'chronology' => 'Saya mengalami kecemasan yang berlebihan ketika harus berinteraksi dengan orang baru atau berada di situasi sosial. Kondisi ini mulai saya rasakan sejak 6 bulan yang lalu dan semakin memburuk. Saya merasa jantung berdebar, tangan berkeringat, dan sulit bernapas ketika harus presentasi di depan kelas atau menghadiri acara sosial.',
                'category' => 'Kecemasan',
                'status' => ComplaintStatus::IN_PROGRESS,
            ],
            [
                'user_id' => $users[0]->uuid,
                'title' => 'Masalah Konsentrasi dalam Belajar',
                'chronology' => 'Beberapa bulan terakhir saya mengalami kesulitan untuk fokus saat belajar. Pikiran saya mudah teralihkan, dan saya merasa tidak produktif. Hal ini dimulai sejak saya harus kuliah online selama pandemi. Saya kesulitan mengatur waktu dan sering menunda-nunda pekerjaan hingga deadline.',
                'category' => 'Akademik',
                'status' => ComplaintStatus::NEW ,
            ],
            [
                'user_id' => count($users) > 1 ? $users[1]->uuid : $users[0]->uuid,
                'title' => 'Stres Akibat Tekanan Pekerjaan',
                'chronology' => 'Pekerjaan saya sangat menuntut dengan banyak deadline yang ketat. Dalam 3 bulan terakhir, saya merasa sangat tertekan dan mulai mengalami gangguan tidur. Saya sering merasa lelah bahkan setelah tidur, mudah marah, dan kehilangan minat pada aktivitas yang biasanya saya nikmati.',
                'category' => 'Pekerjaan',
                'status' => ComplaintStatus::URGENT,
            ],
            [
                'user_id' => count($users) > 1 ? $users[1]->uuid : $users[0]->uuid,
                'title' => 'Kesulitan Mengelola Emosi',
                'chronology' => 'Saya merasa emosi saya tidak stabil. Kadang saya tiba-tiba merasa sangat sedih tanpa alasan yang jelas, dan di lain waktu saya bisa sangat marah karena hal-hal kecil. Ini sudah berlangsung selama 2 bulan dan mulai mempengaruhi hubungan saya dengan keluarga dan teman-teman.',
                'category' => 'Emosi',
                'status' => ComplaintStatus::COMPLETED,
            ],
            [
                'user_id' => count($users) > 2 ? $users[2]->uuid : $users[0]->uuid,
                'title' => 'Trauma Pasca Kecelakaan',
                'chronology' => 'Saya mengalami kecelakaan mobil 4 bulan yang lalu. Sejak saat itu, saya sering mengalami mimpi buruk tentang kecelakaan tersebut, merasa sangat cemas saat berada di dalam mobil, dan terkadang mengalami flashback. Saya juga menjadi lebih mudah terkejut dan sulit tidur.',
                'category' => 'Trauma',
                'status' => ComplaintStatus::IN_PROGRESS,
            ],
        ];

        foreach ($complaintsData as $complaintData) {
            // Create complaint
            $complaint = Complaints::create($complaintData);

            // Add chat messages for this complaint
            $this->createChatMessages($complaint, $admin);

            $this->command->info("Created complaint: {$complaint->title}");
        }

        $this->command->info('Complaints with chat messages seeded successfully!');
    }

    /**
     * Create chat messages for a complaint
     */
    private function createChatMessages(Complaints $complaint, User $admin): void
    {
        $chatMessages = [];

        switch ($complaint->status) {
            case ComplaintStatus::NEW:
                // Only initial message from user
                $chatMessages = [
                    [
                        'sender_id' => $complaint->user_id,
                        'sender_type' => 'user',
                        'message_text' => 'Halo, saya ingin berkonsultasi mengenai masalah yang saya alami.',
                        'message_type' => 'text',
                        'is_read' => false,
                        'created_at' => now(),
                    ],
                ];
                break;

            case ComplaintStatus::IN_PROGRESS:
                // Conversation between user and admin
                $chatMessages = [
                    [
                        'sender_id' => $complaint->user_id,
                        'sender_type' => 'user',
                        'message_text' => 'Halo, saya membutuhkan bantuan untuk masalah yang saya alami.',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subDays(2),
                    ],
                    [
                        'sender_id' => $admin->uuid,
                        'sender_type' => 'admin',
                        'message_text' => 'Selamat datang di MindfulMe. Saya akan membantu Anda. Bisakah Anda ceritakan lebih detail tentang keluhan yang Anda alami?',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subDays(2)->addHours(1),
                    ],
                    [
                        'sender_id' => $complaint->user_id,
                        'sender_type' => 'user',
                        'message_text' => 'Saya sudah menjelaskan di keluhan awal. Apakah ada solusi yang bisa saya lakukan?',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subDays(1),
                    ],
                    [
                        'sender_id' => $admin->uuid,
                        'sender_type' => 'admin',
                        'message_text' => 'Terima kasih atas informasinya. Saya sedang meninjau kasus Anda dan akan segera memberikan rekomendasi terbaik.',
                        'message_type' => 'text',
                        'is_read' => false,
                        'created_at' => now()->subHours(6),
                    ],
                ];
                break;

            case ComplaintStatus::URGENT:
                // Urgent conversation with quick responses
                $chatMessages = [
                    [
                        'sender_id' => $complaint->user_id,
                        'sender_type' => 'user',
                        'message_text' => 'Saya sangat membutuhkan bantuan segera. Situasi saya sangat mendesak.',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subHours(3),
                    ],
                    [
                        'sender_id' => $admin->uuid,
                        'sender_type' => 'admin',
                        'message_text' => 'Kami memahami situasi Anda. Kami akan memprioritaskan kasus Anda dan menghubungkan Anda dengan psikolog kami sesegera mungkin.',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subHours(2)->subMinutes(30),
                    ],
                    [
                        'sender_id' => $admin->uuid,
                        'sender_type' => 'admin',
                        'message_text' => 'Kami telah menjadwalkan sesi konseling untuk Anda. Mohon cek email untuk detail lebih lanjut.',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subHours(2),
                    ],
                    [
                        'sender_id' => $complaint->user_id,
                        'sender_type' => 'user',
                        'message_text' => 'Terima kasih banyak atas respons cepatnya.',
                        'message_type' => 'text',
                        'is_read' => false,
                        'created_at' => now()->subHours(1),
                    ],
                ];
                break;

            case ComplaintStatus::COMPLETED:
                // Complete conversation with resolution
                $chatMessages = [
                    [
                        'sender_id' => $complaint->user_id,
                        'sender_type' => 'user',
                        'message_text' => 'Halo, saya ingin berkonsultasi tentang masalah yang saya hadapi.',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subDays(7),
                    ],
                    [
                        'sender_id' => $admin->uuid,
                        'sender_type' => 'admin',
                        'message_text' => 'Selamat datang! Saya akan membantu Anda. Mari kita diskusikan masalah yang Anda alami.',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subDays(7)->addHours(2),
                    ],
                    [
                        'sender_id' => $complaint->user_id,
                        'sender_type' => 'user',
                        'message_text' => 'Terima kasih. Saya sudah menjelaskan detail masalah saya di keluhan.',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subDays(6),
                    ],
                    [
                        'sender_id' => $admin->uuid,
                        'sender_type' => 'admin',
                        'message_text' => 'Berdasarkan keluhan Anda, saya merekomendasikan untuk melakukan konseling. Kami akan menghubungkan Anda dengan psikolog kami.',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subDays(5),
                    ],
                    [
                        'sender_id' => $complaint->user_id,
                        'sender_type' => 'user',
                        'message_text' => 'Baik, saya setuju. Kapan bisa dilakukan sesi konseling?',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subDays(4),
                    ],
                    [
                        'sender_id' => $admin->uuid,
                        'sender_type' => 'admin',
                        'message_text' => 'Sesi telah dijadwalkan. Semoga konseling berjalan lancar!',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subDays(3),
                    ],
                    [
                        'sender_id' => $complaint->user_id,
                        'sender_type' => 'user',
                        'message_text' => 'Terima kasih banyak atas bantuannya. Saya merasa jauh lebih baik sekarang.',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subDays(1),
                    ],
                    [
                        'sender_id' => $admin->uuid,
                        'sender_type' => 'admin',
                        'message_text' => 'Senang mendengarnya! Jangan ragu untuk menghubungi kami lagi jika membutuhkan bantuan.',
                        'message_type' => 'text',
                        'is_read' => true,
                        'created_at' => now()->subHours(12),
                    ],
                ];
                break;
        }

        // Insert chat messages
        foreach ($chatMessages as $messageData) {
            Chat::create(array_merge($messageData, [
                'complaint_id' => $complaint->complaint_id,
            ]));
        }
    }
}
