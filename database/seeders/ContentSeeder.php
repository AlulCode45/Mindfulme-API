<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContentCategory;
use App\Models\ContentTag;
use App\Models\Article;
use App\Models\Video;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategories();
        $this->seedTags();
        $this->seedArticles();
        $this->seedVideos();
    }

    private function seedCategories(): void
    {
        $categories = [
            [
                'name' => 'Meditasi',
                'slug' => 'meditasi',
                'description' => 'Teknik meditasi dan mindfulness untuk ketenangan',
                'color' => 'blue',
                'icon' => '🧘',
                'is_active' => true,
            ],
            [
                'name' => 'Anxiety',
                'slug' => 'anxiety',
                'description' => 'Mengelola anxiety dan stres',
                'color' => 'orange',
                'icon' => '😰',
                'is_active' => true,
            ],
            [
                'name' => 'Depression',
                'slug' => 'depression',
                'description' => 'Dukungan untuk depression dan mood improvement',
                'color' => 'purple',
                'icon' => '😔',
                'is_active' => true,
            ],
            [
                'name' => 'Sleep',
                'slug' => 'sleep',
                'description' => 'Tidur yang berkualitas dan kesehatan mental',
                'color' => 'indigo',
                'icon' => '😴',
                'is_active' => true,
            ],
            [
                'name' => 'Relationships',
                'slug' => 'relationships',
                'description' => 'Hubungan sehat dan interpersonal skills',
                'color' => 'pink',
                'icon' => '💑',
                'is_active' => true,
            ],
            [
                'name' => 'Self-Care',
                'slug' => 'self-care',
                'description' => 'Merawat diri sendiri dan personal growth',
                'color' => 'green',
                'icon' => '🌱',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ContentCategory::create($category);
        }

        $this->command->info('Content categories seeded successfully.');
    }

    private function seedTags(): void
    {
        $tags = [
            ['name' => 'meditasi', 'slug' => 'meditasi', 'color' => 'blue'],
            ['name' => 'stres', 'slug' => 'stres', 'color' => 'red'],
            ['name' => 'kesehatan-mental', 'slug' => 'kesehatan-mental', 'color' => 'purple'],
            ['name' => 'anxiety', 'slug' => 'anxiety', 'color' => 'orange'],
            ['name' => 'kerja', 'slug' => 'kerja', 'color' => 'gray'],
            ['name' => 'depression', 'slug' => 'depression', 'color' => 'purple'],
            ['name' => 'terapi', 'slug' => 'terapi', 'color' => 'green'],
            ['name' => 'cbt', 'slug' => 'cbt', 'color' => 'blue'],
            ['name' => 'pemula', 'slug' => 'pemula', 'color' => 'yellow'],
            ['name' => 'guided', 'slug' => 'guided', 'color' => 'blue'],
            ['name' => 'pernapasan', 'slug' => 'pernapasan', 'color' => 'cyan'],
            ['name' => 'relaksasi', 'slug' => 'relaksasi', 'color' => 'green'],
            ['name' => 'tidur', 'slug' => 'tidur', 'color' => 'indigo'],
            ['name' => 'mindfulness', 'slug' => 'mindfulness', 'color' => 'purple'],
            ['name' => 'insomnia', 'slug' => 'insomnia', 'color' => 'red'],
        ];

        foreach ($tags as $tag) {
            ContentTag::create($tag);
        }

        $this->command->info('Content tags seeded successfully.');
    }

    private function seedArticles(): void
    {
        // Get admin user for author
        $author = User::where('email', 'like', '%admin%')->first();
        if (!$author) {
            $author = User::first();
        }

        if (!$author) {
            $this->command->error('No user found to assign as article author.');
            return;
        }

        $meditationCategory = ContentCategory::where('slug', 'meditasi')->first();
        $anxietyCategory = ContentCategory::where('slug', 'anxiety')->first();
        $depressionCategory = ContentCategory::where('slug', 'depression')->first();

        $articles = [
            [
                'title' => '5 Teknik Meditasi untuk Mengurangi Stres',
                'slug' => 'teknik-meditasi-stres',
                'excerpt' => 'Pelajari teknik meditasi sederhana yang dapat membantu mengurangi stres dan meningkatkan kesejahteraan mental Anda.',
                'content' => $this->getSampleArticleContent('meditasi'),
                'category_id' => $meditationCategory->id,
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(5),
                'view_count' => 1250,
                'read_time_minutes' => 8,
                'seo_title' => '5 Teknik Meditasi Efektif untuk Mengurangi Stres',
                'seo_description' => 'Pelajari teknik meditasi sederhana dan efektif untuk mengurangi stres sehari-hari dan meningkatkan kesejahteraan mental.',
                'seo_keywords' => 'meditasi, stres, mindfulness, kesehatan mental, relaksasi',
                'tags' => [1, 2, 3],
            ],
            [
                'title' => 'Cara Mengatasi Anxiety di Tempat Kerja',
                'slug' => 'mengatasi-anxiety-tempat-kerja',
                'excerpt' => 'Strategi praktis untuk mengelola anxiety dan stres di lingkungan kerja yang modern.',
                'content' => $this->getSampleArticleContent('anxiety'),
                'category_id' => $anxietyCategory->id,
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(4),
                'view_count' => 890,
                'read_time_minutes' => 6,
                'seo_title' => 'Mengatasi Anxiety di Tempat Kerja',
                'seo_description' => 'Strategi praktis dan efektif untuk mengelola anxiety di lingkungan kerja modern.',
                'seo_keywords' => 'anxiety, kerja, stres, workplace, kesehatan mental',
                'tags' => [4, 5, 3],
            ],
            [
                'title' => 'Manfaat Terapi CBT untuk Depression',
                'slug' => 'manfaat-terapi-cbt-depression',
                'excerpt' => 'Cognitive Behavioral Therapy (CBT) terbukti efektif untuk mengatasi depression. Pelajari cara kerjanya.',
                'content' => $this->getSampleArticleContent('cbt'),
                'category_id' => $depressionCategory->id,
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(3),
                'view_count' => 2100,
                'read_time_minutes' => 10,
                'seo_title' => 'Manfaat Terapi CBT untuk Depression',
                'seo_description' => 'Pelajari bagaimana Cognitive Behavioral Therapy dapat membantu mengatasi depression.',
                'seo_keywords' => 'CBT, depression, terapi, kesehatan mental, psikoterapi',
                'tags' => [6, 7, 8],
            ],
        ];

        foreach ($articles as $articleData) {
            $article = Article::create([
                'id' => Str::uuid(),
                'title' => $articleData['title'],
                'slug' => $articleData['slug'],
                'excerpt' => $articleData['excerpt'],
                'content' => $articleData['content'],
                'author_id' => $author->uuid,
                'category_id' => $articleData['category_id'],
                'status' => $articleData['status'],
                'published_at' => $articleData['published_at'],
                'view_count' => $articleData['view_count'],
                'read_time_minutes' => $articleData['read_time_minutes'],
                'seo_title' => $articleData['seo_title'],
                'seo_description' => $articleData['seo_description'],
                'seo_keywords' => $articleData['seo_keywords'],
            ]);

            // Attach tags
            if (!empty($articleData['tags'])) {
                $article->tags()->attach($articleData['tags']);
            }
        }

        $this->command->info('Articles seeded successfully.');
    }

    private function seedVideos(): void
    {
        // Get admin user for author
        $author = User::where('email', 'like', '%admin%')->first();
        if (!$author) {
            $author = User::first();
        }

        if (!$author) {
            $this->command->error('No user found to assign as video author.');
            return;
        }

        $meditationCategory = ContentCategory::where('slug', 'meditasi')->first();
        $anxietyCategory = ContentCategory::where('slug', 'anxiety')->first();
        $sleepCategory = ContentCategory::where('slug', 'sleep')->first();

        $videos = [
            [
                'title' => 'Guided Meditation: 10 Minutes for Beginners',
                'slug' => 'guided-meditation-10-menit',
                'description' => 'Panduan meditasi 10 menit untuk pemula dengan instruktur suara yang menenangkan.',
                'video_url' => 'https://www.youtube.com/watch?v=example1',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1588286840104-8957b019727f?w=640&h=360&fit=crop',
                'duration_seconds' => 600,
                'category_id' => $meditationCategory->id,
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(2),
                'view_count' => 3500,
                'transcript' => 'Selamat datang di sesi meditasi 10 menit ini. Mari kita mulai dengan menemukan posisi yang nyaman...',
                'tags' => [1, 9, 10],
            ],
            [
                'title' => 'Breathing Exercises for Anxiety Relief',
                'slug' => 'breathing-exercises-anxiety',
                'description' => 'Teknik pernapasan yang efektif untuk mengurangi anxiety dan menenangkan pikiran.',
                'video_url' => 'https://www.youtube.com/watch?v=example2',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=640&h=360&fit=crop',
                'duration_seconds' => 450,
                'category_id' => $anxietyCategory->id,
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(1),
                'view_count' => 2800,
                'transcript' => 'Dalam video ini, saya akan menunjukkan kepada Anda beberapa teknik pernapasan sederhana yang dapat membantu mengurangi anxiety...',
                'tags' => [4, 11, 12],
            ],
            [
                'title' => 'Sleep Better: Mindfulness Techniques',
                'slug' => 'sleep-better-mindfulness',
                'description' => 'Teknik mindfulness untuk meningkatkan kualitas tidur dan mengatasi insomnia.',
                'video_url' => 'https://www.youtube.com/watch?v=example3',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1511884642898-4fb9c7919483?w=640&h=360&fit=crop',
                'duration_seconds' => 720,
                'category_id' => $sleepCategory->id,
                'status' => 'published',
                'published_at' => Carbon::now()->subHours(12),
                'view_count' => 4200,
                'transcript' => 'Tidur yang berkualitas sangat penting untuk kesehatan mental. Mari kita pelajari beberapa teknik mindfulness yang dapat membantu Anda tidur lebih baik...',
                'tags' => [13, 14, 15],
            ],
        ];

        foreach ($videos as $videoData) {
            $video = Video::create([
                'id' => Str::uuid(),
                'title' => $videoData['title'],
                'slug' => $videoData['slug'],
                'description' => $videoData['description'],
                'video_url' => $videoData['video_url'],
                'thumbnail_url' => $videoData['thumbnail_url'],
                'duration_seconds' => $videoData['duration_seconds'],
                'author_id' => $author->uuid,
                'category_id' => $videoData['category_id'],
                'status' => $videoData['status'],
                'published_at' => $videoData['published_at'],
                'view_count' => $videoData['view_count'],
                'transcript' => $videoData['transcript'],
            ]);

            // Attach tags
            if (!empty($videoData['tags'])) {
                $video->tags()->attach($videoData['tags']);
            }
        }

        $this->command->info('Videos seeded successfully.');
    }

    private function getSampleArticleContent($type): string
    {
        $contents = [
            'meditasi' => '<h2>Pendahuluan</h2>
                <p>Meditasi telah terbukti secara ilmiah membantu mengurangi stres dan meningkatkan kesehatan mental. Berikut adalah 5 teknik yang dapat Anda coba:</p>
                <h3>1. Meditasi Pernapasan</h3>
                <p>Fokus pada napas Anda selama 5-10 menit. Rasakan udara masuk dan keluar dari hidung Anda.</p>
                <h3>2. Body Scan Meditation</h3>
                <p>Lakukan pindai dari kepala hingga kaki, perhatikan sensasi di setiap bagian tubuh.</p>
                <h3>3. Meditasi Loving-Kindness</h3>
                <p>Kirim niat baik kepada diri sendiri dan orang lain.</p>
                <h3>4. Walking Meditation</h3>
                <p>Fokus pada setiap langkah saat Anda berjalan perlahan.</p>
                <h3>5. Mindful Eating</h3>
                <p>Makan dengan penuh kesadaran, nikmati setiap gigitan.</p>',
            'anxiety' => '<h2>Pengenalan</h2>
                <p>Anxiety di tempat kerja adalah masalah umum yang dapat mempengaruhi produktivitas dan kesejahteraan.</p>
                <h3>Identifikasi Pemicu</h3>
                <p>Kenali situasi atau pikiran yang memicu anxiety Anda di tempat kerja.</p>
                <h3>Teknik Relaksasi Cepat</h3>
                <p>Gunakan teknik pernapasan dalam saat merasa cemas.</p>
                <h3>Komunikasi Efektif</h3>
                <p>Jelaskan perasaan Anda kepada atasan atau rekan kerja terpercaya.</p>',
            'cbt' => '<h2>Apa itu CBT?</h2>
                <p>Cognitive Behavioral Therapy adalah bentuk terapi yang fokus pada mengubah pola pikiran negatif.</p>
                <h3>Bagaimana CBT Bekerja</h3>
                <p>CBT membantu Anda mengidentifikasi dan mengubah pikiran yang menyebabkan depression.</p>
                <h3>Teknik CBT Umum</h3>
                <ul>
                    <li>Thought recording</li>
                    <li>Cognitive restructuring</li>
                    <li>Behavioral activation</li>
                    <li>Problem solving</li>
                </ul>',
        ];

        return $contents[$type] ?? $contents['meditasi'];
    }
}