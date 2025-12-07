<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ContentCategory;
use App\Models\ContentTag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get news category
        $newsCategory = ContentCategory::where('slug', 'news')->first();
        if (!$newsCategory) {
            $this->command->error('News category not found. Please run NewsCategorySeeder first.');
            return;
        }

        // Get first user
        $user = User::first();
        if (!$user) {
            $this->command->error('No users found in database. Please create a user first.');
            return;
        }

        // Get existing tags
        $tag1 = ContentTag::where('slug', 'kesehatan-mental')->first();
        $tag2 = ContentTag::where('slug', 'wellness')->first();
        $tag3 = ContentTag::where('slug', 'terapi')->first();

        // Create multiple dummy articles
        $articles = [
            [
                'title' => 'Pentingnya Kesehatan Mental di Era Modern',
                'slug' => 'pentingnya-kesehatan-mental-di-era-modern',
                'excerpt' => 'Di tengah kesibukan kehidupan modern, menjaga kesehatan mental menjadi sama pentingnya dengan kesehatan fisik. Artikel ini membahas mengapa kesehatan mental sangat penting dan bagaimana cara menjaganya.',
                'content' => '<h2>Kenapa Kesehatan Mental Penting?</h2><p>Kesehatan mental adalah fondasi dari kesejahteraan holistik. Di era modern yang penuh dengan tekanan dan tantangan, menjaga kesehatan mental menjadi kebutuhan esensial yang tidak bisa diabaikan.</p><h3>Tanda-tanda Kesehatan Mental yang Baik</h3><ul><li>Mampu mengelola stres dengan efektif</li><li>Memiliki hubungan sosial yang sehat</li><li>Beradaptasi dengan perubahan</li><li>Memiliki self-esteem yang positif</li></ul><h3>Tips Menjaga Kesehatan Mental</h3><p>Ada banyak cara untuk menjaga kesehatan mental, antara lain:</p><ol><li><strong>Meditasi dan Mindfulness</strong> - Luangkan waktu 10-15 menit setiap hari untuk meditasi</li><li><strong>Olahraga Teratur</strong> - Aktivitas fisik dapat meningkatkan mood dan mengurangi anxiety</li><li><strong>Tidur Cukup</strong> - Pastikan tidur 7-9 jam setiap malam</li><li><strong>Social Connection</strong> - Jaga hubungan baik dengan keluarga dan teman</li></ol><blockquote>"Kesehatan mental bukanlah tujuan akhir, melainkan perjalanan berkelanjutan." - Ahli Kesehatan Mental</blockquote><p>Ingat, mencari bantuan profesional adalah tanda kekuatan, bukan kelemahan. Jika Anda merasa kesulitan, jangan ragu untuk berkonsultasi dengan ahli kesehatan mental.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'view_count' => 145,
                'read_time_minutes' => 5,
                'seo_title' => 'Pentingnya Kesehatan Mental di Era Modern | MindfulMe',
                'seo_description' => 'Pelajari pentingnya kesehatan mental dan tips menjaganya di era modern. Temukan cara untuk hidup lebih sehat dan bahagia.',
                'seo_keywords' => 'kesehatan mental, wellness, terapi, kesejahteraan, kesehatan fisik',
                'tags' => [$tag1->id, $tag2->id]
            ],
            [
                'title' => '5 Teknik Meditasi untuk Pemula',
                'slug' => '5-teknik-meditasi-untuk-pemula',
                'excerpt' => 'Meditasi adalah praktik sederhana namun kuat untuk meningkatkan kesehatan mental. Artikel ini memperkenalkan 5 teknik meditasi yang mudah dipelajari oleh pemula.',
                'content' => '<h2>Mengapa Meditasi Penting?</h2><p>Meditasi telah terbukti secara ilmiah dapat mengurangi stres, meningkatkan fokus, dan meningkatkan kesejahteraan mental secara keseluruhan. Berikut adalah 5 teknik meditasi yang sempurna untuk pemula:</p><h3>1. Meditasi Pernapasan Dasar</h3><p>Duduk dengan posisi nyaman, fokus pada napas masuk dan keluar. Mulai dengan 5 menit setiap hari.</p><h3>2. Body Scan Meditation</h3><p>Berbaring dan fokus pada setiap bagian tubuh, dari ujung kaki hingga kepala, lepaskan ketegangan di setiap area.</p><h3>3. Meditasi Loving-Kindness</h3><p>Kirim niat baik dan cinta kasih kepada diri sendiri dan orang lain. Ini meningkatkan empati dan mengurangi emosi negatif.</p><h3>4. Walking Meditation</h3><p>Fokus pada langkah kaki, sensasi berjalan, dan lingkungan sekitar. Sempurna untuk mereka yang kesulitan duduk diam.</p><h3>5. Mindful Eating</h3><p>Makan dengan perhatian penuh, nikmati setiap gigitan tanpa gangguan. Ini meningkatkan hubungan dengan makanan dan tubuh.</p><p>Mulai perlahan dan jangan menilai diri sendiri. Konsistensi lebih penting dari durasi.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'view_count' => 89,
                'read_time_minutes' => 4,
                'seo_title' => '5 Teknik Meditasi untuk Pemula | MindfulMe',
                'seo_description' => 'Pelajari 5 teknik meditasi sederhana yang cocok untuk pemula. Tingkatkan kesehatan mental Anda dengan praktik meditasi terbukti.',
                'seo_keywords' => 'meditasi, mindfulness, kesehatan mental, teknik meditasi, pemula',
                'tags' => [$tag1->id, $tag3->id]
            ],
            [
                'title' => 'Manfaat Olahraga untuk Kesehatan Mental',
                'slug' => 'manfaat-olahraga-untuk-kesehatan-mental',
                'excerpt' => 'Olahraga tidak hanya bermanfaat untuk kesehatan fisik, tetapi juga memiliki dampak luar biasa pada kesehatan mental. Temukan berbagai manfaat dan jenis olahraga yang direkomendasikan.',
                'content' => '<h2>Hubungan Antara Olahraga dan Kesehatan Mental</h2><p>Penelitian terus menunjukkan bahwa olahraga memiliki dampak positif yang signifikan pada kesehatan mental. Berikut adalah manfaat utama:</p><h3>1. Mengurangi Stres dan Anxiety</h3><p>Olahraga meningkatkan produksi endorfin, neurotransmitter yang berfungsi sebagai penghilang rasa sakit alami dan peningkat mood.</p><h3>2. Meningkatkan Kualitas Tidur</h3><p>Olahraga teratur dapat membantu mengatur ritme sirkadian dan meningkatkan kualitas tidur, yang penting untuk kesehatan mental.</p><h3>3. Meningkatkan Percaya Diri</h3><p>Mencapai tujuan kebugaran dan melihat peningkatan fisik dapat meningkatkan harga diri dan kepercayaan diri.</p><h3>4. Meningkatkan Fokus dan Konsentrasi</h3><p>Olahraga teratur meningkatkan aliran darah ke otak, yang dapat meningkatkan fungsi kognitif.</p><h3>5. Mengurangi Risiko Depresi</h3><p>Studi menunjukkan bahwa olahraga dapat menjadi efektif seperti antidepresan dalam mengurangi gejala depresi ringan hingga sedang.</p><p>Untuk hasil optimal, cobalah melakukan olahraga moderat selama 30 menit setiap hari.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(7),
                'view_count' => 203,
                'read_time_minutes' => 6,
                'seo_title' => 'Manfaat Olahraga untuk Kesehatan Mental | MindfulMe',
                'seo_description' => 'Temukan bagaimana olahraga dapat meningkatkan kesehatan mental Anda. Pelajari manfaat olahraga dan jenis aktivitas yang direkomendasikan.',
                'seo_keywords' => 'olahraga, kesehatan mental, wellness, fitness, stres',
                'tags' => [$tag2->id, $tag1->id]
            ],
            [
                'title' => 'Cara Memilih Psikolog yang Tepat untuk Anda',
                'slug' => 'cara-memilih-psikolog-yang-tepat',
                'excerpt' => 'Memilih psikolog adalah langkah penting dalam perjalanan kesehatan mental. Artikel ini memberikan panduan lengkap untuk membantu Anda menemukan terapis yang sesuai dengan kebutuhan.',
                'content' => '<h2>Mengapa Memilih Psikolog yang Tepat Penting?</h2><p>Hubungan terapeutik adalah kunci keberhasilan terapi. Psikolog yang tepat dapat membuat perbedaan besar dalam perjalanan penyembuhan Anda.</p><h3>Faktor yang Perlu Dipertimbangkan</h3><h4>1. Kualifikasi dan Lisensi</h4><p>Pastikan psikolog memiliki lisensi resmi dan kualifikasi yang sesuai. Cek sertifikasi dan pengalaman mereka.</p><h4>2. Spesialisasi</h4><p>Psikolog sering memiliki spesialisasi tertentu seperti anxiety, depresi, trauma, atau masalah hubungan.</p><h4>3. Pendekatan Terapi</h4><p>Ada berbagai pendekatan seperti CBT, psikodinamika, atau humanistik. Pilih yang sesuai dengan preferensi Anda.</p><h4>4. Kompatibilitas Personal</h4><p>Rasa nyaman dengan psikolog sangat penting. Jangan ragu untuk "mencoba" beberapa psikolog sebelum memutuskan.</p><h4>5. Logistik</h4><p>Pertimbangkan lokasi, biaya, dan jadwal ketersediaan.</p><h3>Pertanyaan yang Harus Diajukan</h3><ul><li>Apa pengalaman Anda dengan masalah yang saya hadapi?</li><li>Bagaimana pendekatan terapi yang Anda gunakan?</li><li>Berapa lama sesi terapi biasanya berlangsung?</li><li>Bagaimana cara Anda mengukur kemajuan?</li></ul><p>Ingat, mencari bantuan adalah tanda kekuatan, bukan kelemahan.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'view_count' => 67,
                'read_time_minutes' => 7,
                'seo_title' => 'Panduan Memilih Psikolog yang Tepat | MindfulMe',
                'seo_description' => 'Panduan lengkap untuk memilih psikolog atau terapis yang tepat untuk kebutuhan kesehatan mental Anda.',
                'seo_keywords' => 'psikolog, terapi, konseling, kesehatan mental, terapis',
                'tags' => [$tag3->id]
            ],
            [
                'title' => 'Mindfulness di Tempat Kerja: Strategi untuk Mengurangi Burnout',
                'slug' => 'mindfulness-di-tempat-kerja',
                'excerpt' => 'Burnout kerja menjadi masalah umum di era modern. Pelajari bagaimana praktik mindfulness dapat membantu mengurangi stres dan meningkatkan produktivitas di tempat kerja.',
                'content' => '<h2>Apa itu Burnout Kerja?</h2><p>Burnout adalah kondisi kelelahan emosional, fisik, dan mental yang disebabkan oleh stres kronis di tempat kerja. Gejalanya termasuk kelelahan, sinisme, dan penurunan efektivitas.</p><h3>Bagaimana Mindfulness Membantu?</h3><h4>1. Meningkatkan Kesadaran Diri</h4><p>Mindfulness membantu Anda mengenali tanda-tanda awal burnout dan merespons dengan lebih baik.</p><h4>2. Mengurangi Reaktivitas</h4><p>Praktik mindfulness membantu Anda merespons situasi stres dengan tenang alih-alih bereaksi secara impulsif.</p><h4>3. Meningkatkan Fokus</h4><p>Latihan mindfulness dapat meningkatkan kemampuan konsentrasi dan mengurangi gangguan.</p><h3>Strategi Mindfulness di Tempat Kerja</h3><h4>Micro-Mindfulness</h4><p>Ambil 1-2 menit untuk fokus pada napas di tengah hari kerja yang sibuk.</p><h4>Mindful Communication</h4><p>Praktikkan mendengarkan sepenuhnya saat berinteraksi dengan rekan kerja.</p><h4>Body Scan Break</h4><p>Lakukan pemeriksaan singkat tubuh untuk melepaskan ketegangan yang terakumulasi.</p><h4>Mindful Eating Lunch</h4><p>Makan siang tanpa gangguan elektronik untuk mengisi ulang energi mental.</p><h4>Gratitude Practice</h4><p>Akhiri hari kerja dengan mencatat 3 hal yang Anda syukuri.</p><p>Mulai dengan praktik kecil dan konsisten untuk hasil terbaik.</p>',
                'status' => 'draft',
                'published_at' => null,
                'view_count' => 0,
                'read_time_minutes' => 5,
                'seo_title' => 'Mindfulness di Tempat Kerja: Strategi Anti-Burnout | MindfulMe',
                'seo_description' => 'Pelajari strategi mindfulness untuk mencegah dan mengatasi burnout kerja. Tingkatkan kesejahteraan mental di tempat kerja.',
                'seo_keywords' => 'mindfulness, burnout, stres kerja, kesehatan mental, produktivitas',
                'tags' => [$tag1->id, $tag2->id, $tag3->id]
            ]
        ];

        $createdArticles = [];
        foreach ($articles as $articleData) {
            $article = Article::create([
                'id' => Str::uuid()->toString(),
                'title' => $articleData['title'],
                'slug' => $articleData['slug'],
                'excerpt' => $articleData['excerpt'],
                'content' => $articleData['content'],
                'author_id' => $user->uuid,
                'category_id' => $newsCategory->id, // Using the existing news category ID
                'status' => $articleData['status'],
                'published_at' => $articleData['published_at'],
                'view_count' => $articleData['view_count'],
                'read_time_minutes' => $articleData['read_time_minutes'],
                'seo_title' => $articleData['seo_title'],
                'seo_description' => $articleData['seo_description'],
                'seo_keywords' => $articleData['seo_keywords']
            ]);

            // Attach tags
            if (!empty($articleData['tags'])) {
                $article->tags()->attach($articleData['tags']);
            }

            $createdArticles[] = $article;
        }

        $this->command->info(count($createdArticles) . ' dummy articles created successfully:');
        foreach ($createdArticles as $article) {
            $this->command->line('- ' . $article->title . ' (Status: ' . $article->status . ')');
        }
    }
}