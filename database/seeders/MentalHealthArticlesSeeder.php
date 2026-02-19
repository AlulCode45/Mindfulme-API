<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ContentCategory;
use App\Models\ContentTag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MentalHealthArticlesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create article category
        $category = ContentCategory::firstOrCreate(
            ['slug' => 'kesehatan-mental'],
            ['name' => 'Kesehatan Mental']
        );

        // Get first user or create one
        $user = User::first();
        if (!$user) {
            $this->command->error('No users found in database. Creating a test user...');
            $user = User::create([
                'uuid' => Str::uuid()->toString(),
                'name' => 'Admin MindfulMe',
                'email' => 'admin@mindfulme.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Get or create tags
        $tagKesehatan = ContentTag::firstOrCreate(
            ['slug' => 'kesehatan-mental'],
            ['name' => 'Kesehatan Mental']
        );
        $tagWellness = ContentTag::firstOrCreate(
            ['slug' => 'wellness'],
            ['name' => 'Wellness']
        );
        $tagPsikologi = ContentTag::firstOrCreate(
            ['slug' => 'psikologi'],
            ['name' => 'Psikologi']
        );
        $tagDiri = ContentTag::firstOrCreate(
            ['slug' => 'pengembangan-diri'],
            ['name' => 'Pengembangan Diri']
        );
        $tagRelaksasi = ContentTag::firstOrCreate(
            ['slug' => 'relaksasi'],
            ['name' => 'Relaksasi']
        );

        // 10 Mental Health Articles
        $articles = [
            [
                'title' => 'Panduan Lengkap Mengenali Gejala Depresi dan Cara Mengatasinya',
                'slug' => 'panduan-lengkap-mengenali-gejala-depresi',
                'excerpt' => 'Depresi adalah kondisi kesehatan mental yang serius. Artikel ini memberikan informasi lengkap tentang gejala depresi, penyebabnya, dan strategi mengatasi yang efektif.',
                'content' => '<h2>Apa itu Depresi?</h2><p>Depresi adalah gangguan mood yang ditandai dengan perasaan sedih mendalam, kehilangan minat pada aktivitas, dan putus asa yang berkelanjutan. Ini berbeda dari kesedihan biasa karena intensitas dan durasinya.</p><h3>Gejala-Gejala Depresi</h3><ul><li>Perasaan sedih atau kosong yang persisten</li><li>Kehilangan minat pada aktivitas yang biasanya disukai</li><li>Perubahan nafsu makan atau berat badan</li><li>Gangguan tidur (insomnia atau tidur berlebihan)</li><li>Kelelahan atau kurangnya energi</li><li>Perasaan tidak berharga atau rasa bersalah yang berlebihan</li><li>Kesulitan berkonsentrasi atau membuat keputusan</li><li>Pikiran tentang kematian atau bunuh diri</li></ul><h3>Penyebab Depresi</h3><p>Depresi dapat disebabkan oleh kombinasi faktor biologis, psikologis, dan lingkungan:</p><ul><li><strong>Faktor Biologis:</strong> Ketidakseimbangan neurotransmitter, riwayat keluarga</li><li><strong>Faktor Psikologis:</strong> Trauma, benci diri, perfeksionisme</li><li><strong>Faktor Lingkungan:</strong> Stres kronis, kehilangan, isolasi sosial</li></ul><h3>Strategi Mengatasi Depresi</h3><ol><li><strong>Cari Bantuan Profesional:</strong> Konsultasi dengan psikolog atau psikiater sangat penting</li><li><strong>Olahraga Teratur:</strong> Aktivitas fisik dapat meningkatkan mood dan mengurangi gejala</li><li><strong>Tidur Berkualitas:</strong> Jaga rutinitas tidur yang konsisten</li><li><strong>Nutrisi Seimbang:</strong> Makanan yang sehat mendukung kesehatan mental</li><li><strong>Social Connection:</strong> Terhubung dengan keluarga dan teman dapat mengurangi isolasi</li><li><strong>Mindfulness:</strong> Praktik meditasi dan breathing exercise</li><li><strong>Hindari Alkohol dan Narkoba:</strong> Zat-zat ini dapat memperburuk depresi</li></ol><p>Ingat: Depresi adalah penyakit yang dapat disembuhkan. Dengan bantuan profesional dan dukungan sosial, Anda dapat berekoperi dan memulihkan diri.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(10),
                'view_count' => 342,
                'read_time_minutes' => 8,
                'seo_title' => 'Panduan Lengkap Mengenali Gejala Depresi dan Cara Mengatasinya',
                'seo_description' => 'Ketahui gejala depresi, penyebabnya, dan strategi efektif untuk mengatasinya. Dapatkan bantuan dan dukungan untuk kesehatan mental yang lebih baik.',
                'seo_keywords' => 'depresi, gejala depresi, cara mengatasi depresi, kesehatan mental',
                'tags' => [$tagKesehatan->id, $tagPsikologi->id]
            ],
            [
                'title' => 'Teknik Breathing Exercise untuk Mengatasi Anxiety dan Panic Attack',
                'slug' => 'teknik-breathing-exercise-anxiety',
                'excerpt' => 'Anxiety dan panic attack bisa dikelola dengan teknik pernapasan yang tepat. Pelajari berbagai teknik breathing exercise yang mudah dilakukan dan terbukti efektif.',
                'content' => '<h2>Memahami Anxiety dan Panic Attack</h2><p>Anxiety adalah perasaan khawatir atau takut yang intens, sementara panic attack adalah episode sudden intense fear yang disertai gejala fisik. Teknik pernapasan dapat membantu mengatasinya.</p><h3>Mengapa Teknik Pernapasan Efektif?</h3><p>Ketika kita anxious, pernapasan menjadi cepat dan dangkal. Dengan mengontrol pernapasan, kita memberikan sinyal ke otak bahwa kita aman, sehingga menurunkan respons fight-or-flight.</p><h3>5 Teknik Breathing Exercise</h3><h4>1. Box Breathing (4-4-4-4)</h4><p><strong>Cara:</strong> Tarik napas selama 4 detik, tahan 4 detik, hembuskan 4 detik, tahan 4 detik. Ulangi 4-5 kali.</p><p><strong>Manfaat:</strong> Menenangkan sistem saraf dengan cepat</p><h4>2. 4-7-8 Breathing</h4><p><strong>Cara:</strong> Tarik napas 4 detik, tahan 7 detik, hembuskan 8 detik.</p><p><strong>Manfaat:</strong> Sangat efektif untuk menenangkan dan meningkatkan kualitas tidur</p><h4>3. Belly Breathing (Diaphragmatic Breathing)</h4><p><strong>Cara:</strong> Tarik napas melalui hidung sehingga perut membesar, bukan dada. Hembuskan perlahan melalui mulut.</p><p><strong>Manfaat:</strong> Mengaktifkan parasympathetic nervous system</p><h4>4. Alternate Nostril Breathing</h4><p><strong>Cara:</strong> Tutup lubang hidung kanan, tarik napas dari kiri. Tutup kiri, hembuskan dari kanan. Lakukan bergantian.</p><p><strong>Manfaat:</strong> Menyeimbangkan energi dan pikiran</p><h4>5. Extended Exhale Breathing</h4><p><strong>Cara:</strong> Tarik napas dalam 4 detik, hembuskan dalam 6-8 detik. Fokus pada pernafasan yang lebih panjang.</p><p><strong>Manfaat:</strong> Menurunkan detak jantung dengan cepat</p><h3>Tips Praktis</h3><ul><li>Praktik teknik ini setiap hari, tidak hanya saat panic</li><li>Cari tempat yang nyaman dan tenang</li><li>Konsistensi adalah kunci kesuksesan</li><li>Jika anxiety persisten, konsultasi dengan profesional</li></ul>',
                'status' => 'published',
                'published_at' => now()->subDays(8),
                'view_count' => 289,
                'read_time_minutes' => 7,
                'seo_title' => 'Teknik Breathing Exercise Mengatasi Anxiety dan Panic Attack',
                'seo_description' => 'Kuasai teknik breathing exercise untuk mengatasi anxiety dan panic attack. Dapatkan panduan langkah demi langkah yang terbukti efektif.',
                'seo_keywords' => 'breathing exercise, anxiety, panic attack, teknik relaksasi',
                'tags' => [$tagRelaksasi->id, $tagKesehatan->id]
            ],
            [
                'title' => 'Sleep Hygiene: Panduan Meningkatkan Kualitas Tidur untuk Kesehatan Mental',
                'slug' => 'sleep-hygiene-kualitas-tidur',
                'excerpt' => 'Tidur yang berkualitas adalah fondasi kesehatan mental yang baik. Pelajari praktik sleep hygiene yang efektif untuk meningkatkan kualitas tidur Anda.',
                'content' => '<h2>Hubungan Tidur dan Kesehatan Mental</h2><p>Tidur yang kurang dapat memperburuk anxiety, depresi, dan masalah kesehatan mental lainnya. Sebaliknya, tidur yang berkualitas secara signifikan meningkatkan kesehatan mental.</p><h3>Apa itu Sleep Hygiene?</h3><p>Sleep hygiene adalah serangkaian kebiasaan dan praktik yang dapat digunakan untuk meningkatkan kualitas tidur malam. Ini termasuk lingkungan tidur dan rutinitas sebelum tidur.</p><h3>Praktik Sleep Hygiene Terbaik</h3><h4>Lingkungan Tidur</h4><ul><li><strong>Suhu:</strong> Kamar harus dingin (sekitar 16-19°C ideal untuk tidur)</li><li><strong>Gelap:</strong> Gunakan blackout curtains untuk gelap total</li><li><strong>Hening:</strong> Gunakan earplugs atau white noise jika diperlukan</li><li><strong>Nyaman:</strong> Investasi dalam kasur dan bantal berkualitas</li></ul><h4>Rutinitas Sebelum Tidur</h4><ul><li><strong>Konsisten:</strong> Tidur dan bangun pada waktu yang sama setiap hari</li><li><strong>Wind-down 1 jam sebelum:</strong> Kurangi stimulasi dari layar elektronik</li><li><strong>Relaksasi:</strong> Membaca, meditasi, atau mandi air hangat</li><li><strong>Hindari kafein:</strong> Jangan konsumsi kafein 6 jam sebelum tidur</li></ul><h4>Gaya Hidup</h4><ul><li><strong>Olahraga:</strong> Aktivitas fisik teratur meningkatkan tidur, tapi hindari dekat waktu tidur</li><li><strong>Paparan Sinar Matahari:</strong> Dapatkan cahaya alami di pagi hari</li><li><strong>Hindari Alkohol:</strong> Meski membuat mengantuk, alkohol mengganggu kualitas tidur</li><li><strong>Makanan Ringan:</strong> Hindari makanan berat 3 jam sebelum tidur</li></ul><h3>Makanan yang Mendukung Tidur</h3><ul><li>Almond (kaya akan magnesium)</li><li>Salmon (mengandung omega-3)</li><li>Teh chamomile atau valerian</li><li>Pisang (kaya akan potassium)</li><li>Yogurt (mengandung tryptophan)</li></ul><h3>Yang Harus Dihindari</h3><ul><li>Kafein setelah siang</li><li>Alkohol sebelum tidur</li><li>Layar elektronik 1 jam sebelum tidur</li><li>Tidur siang terlalu lama (max 30 menit)</li><li>Pekerjaan atau aktivitas yang merangsang pikiran</li></ul><p><strong>Target:</strong> 7-9 jam tidur berkualitas setiap malam untuk kesehatan mental optimal.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(6),
                'view_count' => 256,
                'read_time_minutes' => 8,
                'seo_title' => 'Sleep Hygiene: Panduan Meningkatkan Kualitas Tidur untuk Kesehatan Mental',
                'seo_description' => 'Tingkatkan kualitas tidur dengan praktik sleep hygiene terbaik. Pelajari cara menciptakan lingkungan dan rutinitas tidur yang ideal.',
                'seo_keywords' => 'sleep hygiene, kualitas tidur, insomnia, kesehatan mental',
                'tags' => [$tagWellness->id, $tagKesehatan->id]
            ],
            [
                'title' => 'Mengatasi Fear of Failure: Ubah Perfeksionisme Menjadi Produktivitas',
                'slug' => 'mengatasi-fear-of-failure',
                'excerpt' => 'Takut gagal dan perfeksionisme dapat menghambat pertumbuhan pribadi. Pelajari strategi untuk mengatasi fear of failure dan mengembangkan growth mindset.',
                'content' => '<h2>Apa itu Fear of Failure?</h2><p>Fear of failure adalah ketakutan intens terhadap kegagalan yang dapat menyebabkan prokrastinasi, anxiety, dan menghindari tantangan. Ini sering dikaitkan dengan perfeksionisme yang ekstrem.</p><h3>Perbedaan Perfeksionisme Sehat dan Tidak Sehat</h3><table border="1"><tr><th>Perfeksionisme Sehat</th><th>Perfeksionisme Tidak Sehat</th></tr><tr><td>Menetapkan standar yang realistis</td><td>Standar yang tidak mungkin dicapai</td></tr><tr><td>Belajar dari kegagalan</td><td>Takut terhadap kegagalan</td></tr><tr><td>Fleksibel saat dibutuhkan</td><td>Tidak dapat berkompromi</td></tr><tr><td>Meningkatkan motivasi</td><td>Menyebabkan anxiety</td></tr></table><h3>Dampak Negatif Fear of Failure</h3><ul><li>Prokrastinasi dan penghindaran tugas</li><li>Anxiety dan stress kronis</li><li>Rendah diri dan self-doubt</li><li>Terhambat dalam pertumbuhan personal</li><li>Hubungan yang strained</li></ul><h3>Strategi Mengatasi Fear of Failure</h3><h4>1. Ubah Perspektif tentang Kegagalan</h4><p><strong>Mindset Lama:</strong> Gagal artinya saya tidak cukup baik.</p><p><strong>Mindset Baru:</strong> Gagal adalah bagian dari proses belajar yang penting.</p><h4>2. Visualisasi Best Case Scenario</h4><p>Alih-alih membayangkan hal buruk yang bisa terjadi, bayangkan hasil positif yang bisa Anda capai.</p><h4>3. Mulai dari Hal Kecil</h4><p>Ambil langkah kecil untuk membangun confidence. Setiap kecil keberhasilan akan memperkuat kepercayaan diri Anda.</p><h4>4. Lepaskan Kontrol Sempurna</h4><p>Terima bahwa tidak ada yang sempurna. "Done is better than perfect" untuk membangun momentum.</p><h4>5. Kelilingi Diri dengan Orang Positif</h4><p>Orang-orang yang mendukung dapat membantu Anda melihat dari perspektif berbeda.</p><h4>6. Praktikkan Self-Compassion</h4><p>Perlakukan diri Anda seperti teman baik. Berikan diri Anda sendiri grace yang sama yang Anda berikan orang lain.</p><h3>Afirmasi Efektif</h3><ul><li>"Saya belajar dari kegagalan, bukan didefinisikan olehnya"</li><li>"Saya layak mencoba, bahkan jika mungkin gagal"</li><li>"Risikonya layak diambil untuk pertumbuhan saya"</li><li>"Kegagalan adalah kesempatan untuk belajar"</li></ul>',
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'view_count' => 198,
                'read_time_minutes' => 7,
                'seo_title' => 'Mengatasi Fear of Failure: Ubah Perfeksionisme Menjadi Produktivitas',
                'seo_description' => 'Pelajari cara mengatasi fear of failure dan mengubah perfeksionisme ekstrem menjadi motivasi produktif dengan strategi terbukti.',
                'seo_keywords' => 'fear of failure, perfeksionisme, growth mindset, kesejahteraan mental',
                'tags' => [$tagDiri->id, $tagKesehatan->id]
            ],
            [
                'title' => 'Membangun Hubungan Sosial yang Sehat untuk Kesehatan Mental',
                'slug' => 'membangun-hubungan-sosial-sehat',
                'excerpt' => 'Hubungan sosial yang bermakna adalah kunci kesehatan mental yang kuat. Pelajari cara membangun dan mempertahankan hubungan yang sehat dan mendukung.',
                'content' => '<h2>Pentingnya Koneksi Sosial</h2><p>Manusia adalah makhluk sosial. Koneksi sosial yang bermakna berkorelasi erat dengan kesejahteraan mental, kehidupan yang lebih panjang, dan tingkat kebahagiaan yang lebih tinggi.</p><h3>Dampak Isolasi Sosial</h3><ul><li>Meningkatkan risiko depresi dan anxiety</li><li>Meningkatkan stress dan cortisol</li><li>Menurunkan sistem imun</li><li>Meningkatkan risiko penyakit kardiovaskular</li><li>Accelerates cognitive decline</li></ul><h3>Ciri-Ciri Hubungan Sosial yang Sehat</h3><ul><li>Saling menghormati dan mendengarkan</li><li>Kepercayaan dan transparansi</li><li>Dukungan emosional tanpa conditional</li><li>Boundary yang jelas namun fleksibel</li><li>Kemampuan untuk mengkomunikasikan perbedaan</li><li>Pertumbuhan dan evolusi bersama</li></ul><h3>Jenis Hubungan yang Penting</h3><h4>Hubungan Keluarga</h4><p>Fondasi dukungan emosional awal. Penting untuk menerima dan memberikan cinta unconditional.</p><h4>Persahabatan</h4><p>Hubungan yang dipilih yang memberikan dukungan, kesenangan, dan pemahaman.</p><h4>Hubungan Romantis</h4><p>Partnership yang memberikan intimacy dan dukungan berkelanjutan.</p><h4>Komunitas dan Jaringan Sosial</h4><p>Perasaan belonging dan tujuan bersama.</p><h3>Strategi Membangun Hubungan yang Sehat</h3><h4>1. Prioritaskan Kualitas daripada Kuantitas</h4><p>Memiliki beberapa hubungan yang mendalam lebih baik daripada banyak hubungan yang superfisial.</p><h4>2. Praktek Active Listening</h4><p>Dengarkan untuk memahami, bukan untuk membalas. Tunjukkan empati dan perhatian.</p><h4>3. Luangkan Waktu Berkualitas</h4><p>Device-free time bersama orang-orang yang penting untuk Anda.</p><h4>4. Komunikasi yang Jujur</h4><p>Ekspresikan perasaan dengan jelas, tidak agresif, dan dengan rasa hormat.</p><h4>5. Tetapkan Boundary yang Sehat</h4><p>Cukup baik untuk diri sendiri dan orang lain. Tidak semua orang layak mendapat akses penuh ke Anda.</p><h4>6. Praktikkan Forgiveness</h4><p>Belajar memaafkan diri sendiri dan orang lain. Dendam hanya merugikan Anda.</p><h4>7. Dukung Pertumbuhan Satu Sama Lain</h4><p>Rayakan kesuksesan dan dukung selama tantangan.</p><h3>Mengatasi Loneliness</h3><ul><li>Ambil langkah pertama dan hubungi orang</li><li>Join komunitas atau klub yang sesuai minat Anda</li><li>Relawan untuk membantu orang lain</li><li>Praktikkan mindfulness untuk menerima dan memproses perasaan</li><li>Cari bantuan profesional jika loneliness persisten</li></ul>',
                'status' => 'published',
                'published_at' => now()->subDays(4),
                'view_count' => 276,
                'read_time_minutes' => 8,
                'seo_title' => 'Membangun Hubungan Sosial yang Sehat untuk Kesehatan Mental',
                'seo_description' => 'Pelajari cara membangun hubungan sosial yang bermakna dan sehat. Koneksi sosial yang kuat adalah kunci kesejahteraan mental.',
                'seo_keywords' => 'hubungan sosial, koneksi sosial, loneliness, kesehatan mental, wellness',
                'tags' => [$tagKesehatan->id, $tagWellness->id]
            ],
            [
                'title' => 'Self-Care: Lebih dari Sekedar Luxury Baths dan Wine',
                'slug' => 'self-care-kesehatan-mental',
                'excerpt' => 'Self-care yang sejati adalah tentang memprioritaskan kesehatan mental dan fisik Anda. Pelajari praktik self-care yang bermakna dan sustainable untuk kehidupan sehari-hari.',
                'content' => '<h2>Apa Itu Self-Care yang Sejati?</h2><p>Self-care sering dipahami salah sebagai indulgensi atau luxury. Sebenarnya, self-care adalah tindakan yang intentional untuk menjaga kesehatan fisik, mental, dan emosional Anda.</p><h3>Mengapa Self-Care Penting?</h3><ul><li>Mengurangi stress dan burnout</li><li>Meningkatkan kesadaran diri</li><li>Meningkatkan resilience mental</li><li>Mencegah depresi dan anxiety</li><li>Meningkatkan self-esteem</li><li>Memungkinkan Anda menjadi versi terbaik untuk orang lain</li></ul><h3>5 Pilar Self-Care</h3><h4>1. Physical Self-Care</h4><ul><li>Olahraga teratur</li><li>Nutrisi seimbang</li><li>Tidur yang cukup</li><li>Pemeriksaan kesehatan rutin</li><li>Hygiene personal</li><li>Mengurangi alkohol dan obat-obatan</li></ul><h4>2. Mental Self-Care</h4><ul><li>Meditasi dan mindfulness</li><li>Journaling</li><li>Membaca buku</li><li>Puzzle atau game cerebral</li><li>Belajar hal baru</li><li>Limit news consumption</li></ul><h4>3. Emotional Self-Care</h4><ul><li>Mengekspresikan perasaan dengan sehat</li><li>Boundaries setting</li><li>Mencari terapi atau counseling</li><li>Creative expression (art, music, writing)</li><li>Membiarkan diri menangis atau marah</li><li>Memproses trauma dengan sehat</li></ul><h4>4. Social Self-Care</h4><ul><li>Waktu berkualitas dengan orang terkasih</li><li>Menjauh dari toxic relationships</li><li>Volunteer dan membantu komunitas</li><li>Networking dan terhubung dengan people</li><li>Join groups atau communities</li></ul><h4>5. Spiritual Self-Care</h4><ul><li>Meditasi atau prayer</li><li>Berada di alam</li><li>Refleksi dan gratitude</li><li>Mengikuti nilai dan purpose Anda</li><li>Yoga atau tai chi</li></ul><h3>Self-Care adalah Ongoing Process</h3><p>Self-care bukan destinasi atau checklist yang akan diselesaikan. Ini adalah komitmen berkelanjutan terhadap kesejahteraan Anda. Apa yang berhasil untuk Anda hari ini mungkin berbeda bulan depan, dan itu OK.</p><h3>Mengatasi Self-Care Guilt</h3><p>Banyak orang merasa bersalah saat mengutamakan self-care. Ingat:</p><ul><li>Anda tidak egois karena menjaga diri sendiri</li><li>Anda tidak dapat minum dari gelas kosong</li><li>Menjaga kesehatan mental Anda membuat Anda lebih baik untuk orang lain</li></ul><h3>Self-Care Planning</h3><p><strong>Buat Self-Care Plan Pribadi:</strong></p><ol><li>Identifikasi 3 area yang paling membutuhkan attention</li><li>Pilih 2-3 activities untuk setiap area</li><li>Schedule activities ini seperti appointment penting</li><li>Start small dan consistency adalah kunci</li><li>Review dan adjust sesuai kebutuhan</li></ol>',
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'view_count' => 312,
                'read_time_minutes' => 8,
                'seo_title' => 'Self-Care: Lebih dari Sekedar Luxury Baths dan Wine | MindfulMe',
                'seo_description' => 'Pahami arti self-care yang sejati dan pelajari 5 pilar penting untuk menjaga kesehatan mental dan emosional Anda.',
                'seo_keywords' => 'self-care, wellness, kesehatan mental, self-love, mental health',
                'tags' => [$tagWellness->id, $tagDiri->id, $tagKesehatan->id]
            ],
            [
                'title' => 'Cognitive Behavioral Therapy (CBT): Mengubah Pikiran Negatif Menjadi Positif',
                'slug' => 'cognitive-behavioral-therapy-cbt',
                'excerpt' => 'CBT adalah terapi yang terbukti efektif untuk berbagai masalah kesehatan mental. Pelajari prinsip-prinsip CBT dan cara menggunakannya dalam kehidupan sehari-hari.',
                'content' => '<h2>Apa itu Cognitive Behavioral Therapy (CBT)?</h2><p>CBT adalah pendekatan psikoterapi yang berfokus pada hubungan antara pikiran, perasaan, dan perilaku. Prinsip dasarnya adalah dengan mengubah pola pikiran negatif, kita dapat mengubah perasaan dan perilaku.</p><h3>Dasar Teori CBT</h3><p><strong>Cognitive Triangle:</strong></p><pre>        PIKIRAN (Thoughts)
           /              \\
          /                \\
      PERASAAN          PERILAKU
      (Feelings)       (Behavior)</pre><p>Ketiga elemen ini saling mempengaruhi. Dengan mengubah satu, kita dapat mengubah dua lainnya.</p><h3>Apakah CBT Efektif?</h3><p>CBT telah terbukti melalui ribuan penelitian ilmiah efektif untuk:</p><ul><li>Depresi dan anxiety</li><li>PTSD dan trauma</li><li>OCD dan panic disorder</li><li>Masalah tidur</li><li>Masalah hubungan</li><li>Addiction dan substance abuse</li><li>Eating disorders</li></ul><h3>5 Langkah Proses CBT</h3><h4>1. Identifikasi Pikiran Otomatis Negatif</h4><p>Pikiran otomatis adalah pikiran yang muncul tanpa sadar. Contoh: "Aku akan gagal", "Tidak ada yang menyukaiku".</p><h4>2. Tantang Pikiran Tersebut</h4><p>Ajukan pertanyaan: Apa buktinya? Apakah ini benar? Apakah ada pikiran alternatif yang lebih akurat?</p><h4>3. Ganti dengan Pikiran yang Lebih Seimbang</h4><p>Buat pernyataan yang lebih realistis dan positif: "Saya bisa berhasil jika saya mencoba" atau "Aku cukup baik seperti apa saya sekarang".</p><h4>4. Ubah Perilaku</h4><p>Lakukan tindakan yang konsisten dengan pikiran baru Anda. Jika Anda berpikir Anda bisa mencoba, ambil langkah kecil.</p><h4>5. Monitor Hasil</h4><p>Catat bagaimana perubahan pikiran dan perilaku mempengaruhi perasaan Anda.</p><h3>Teknik CBT Praktis</h3><h4>Thought Records</h4><p>Catat:</p><ol><li>Event atau situasi yang memicu</li><li>Pikiran otomatis yang muncul</li><li>Emosi dan intensitasnya</li><li>Bukti untuk dan melawan pikiran</li><li>Pikiran alternatif yang lebih balanced</li></ol><h4>Behavioral Activation</h4><p>Lakukan aktivitas yang positif bahkan saat tidak merasa ingin melakukannya. Tindakan sering mendahului perasaan.</p><h4>Problem-Solving</h4><p>Identifikasi masalah, brainstorm solusi, pilih yang terbaik, implementasi, dan evaluate hasilnya.</p><h3>Melengkapi CBT</h3><p>Sementara banyak orang menemukan CBT sangat membantu, sebaiknya dikombinasikan dengan:</p><ul><li>Olahraga dan nutrisi</li><li>Mindfulness dan meditasi</li><li>Social support</li><li>Sleep dan stress management</li><li>Ketika diperlukan, medication</li></ul><p><strong>Kesimpulan:</strong> CBT adalah alat kuat untuk mengubah pola pikiran yang tidak membantu. Dengan praktik konsisten, Anda dapat menguasai teknik ini dan melihat perubahan nyata dalam kesejahteraan mental Anda.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'view_count' => 267,
                'read_time_minutes' => 9,
                'seo_title' => 'Cognitive Behavioral Therapy (CBT): Ubah Pikiran Negatif Positif',
                'seo_description' => 'Pahami Cognitive Behavioral Therapy (CBT) dan pelajari teknik-teknik praktis untuk mengubah pola pikiran negatif menjadi positif.',
                'seo_keywords' => 'CBT, cognitive behavioral therapy, terapi, kesehatan mental, pikiran positif',
                'tags' => [$tagPsikologi->id, $tagKesehatan->id]
            ],
            [
                'title' => 'Mindfulness di Era Digital: Tetap Fokus Ditengah Distraksi Digital',
                'slug' => 'mindfulness-era-digital',
                'excerpt' => 'Era digital membawa banyak distraksi yang mengganggu kesehatan mental. Pelajari strategi mindfulness untuk tetap fokus dan present di tengah overhead informasi.',
                'content' => '<h2>Challenge Era Digital</h2><p>Kita hidup di era di mana informasi terus mengalir 24/7. Rata-rata orang mengecek ponsel mereka 150 kali sehari. Ini menciptakan constant state of distraction yang berdampak negatif pada kesehatan mental.</p><h3>Dampak Negative Screen Time</h3><ul><li>Attention span yang menurun</li><li>Anxiety dan FOMO (Fear of Missing Out)</li><li>Sleep disruption</li><li>Reduced social connection (paradoxically)</li><li>Increased comparison dan low self-esteem</li><li>Digital fatigue dan burnout</li></ul><h3>Mindfulness sebagai Solusi</h3><p>Mindfulness - kesadaran akan momen present - adalah antidot efektif terhadap overstimulation digital.</p><h3>Praktik Mindfulness di Era Digital</h3><h4>1. Digital Detox</h4><p><strong>Pilihan 1 - Cold Turkey:</strong> Ambil hari atau weekend tanpa teknologi.</p><p><strong>Pilihan 2 - Gradual:</strong> Mulai dengan 1 jam screen-free per hari, kemudian increase.</p><p><strong>Pilihan 3 - Selective:</strong> Hapus app yang paling addictive.</p><h4>2. Mindful Phone Use</h4><ul><li><strong>Intention Setting:</strong> Sebelum membuka app, tanya "Untuk apa?" dan "Berapa lama?"</li><li><strong>Notification Management:</strong> Turn off semua notification kecuali yang essential</li><li><strong>App Limitation:</strong> Set time limits pada app yang membuat addicted</li><li><strong>Grayscale Mode:</strong> Ubah display ke grayscale untuk mengurangi appeal</li></ul><h4>3. Mindful Scrolling</h4><p>Jika Anda memilih untuk online:</p><ul><li>Perhatikan bagaimana Anda merasa saat scrolling. Apakah happy, anxious, atau envious?</li><li>Unfollow akun yang membuat Anda merasa buruk tentang diri sendiri</li><li>Follow akun yang inspiratif dan educational</li><li>Set timer untuk session tertentu</li></ul><h4>4. Presence Practices</h4><ul><li><strong>Device-free meals:</strong> Tidak ada phone saat makan</li><li><strong>Device-free time with people:</strong> Saat bersama orang, phone tetap di tas</li><li><strong>Morning routine tanpa phone:</strong> Jangan cek email atau social media dalam 1 jam setelah bangun</li><li><strong>Evening wind-down tanpa screen:</strong> 1-2 jam sebelum tidur, tidak ada screen</li></ul><h4>5. Mindfulness Meditation untuk Digital Detox</h4><p><strong>Body Scan:</strong> Saat merasa urge untuk cek phone, lakukan body scan 5 menit sebagai ganti.</p><p><strong>Urge Surfing:</strong> Observe keinginan untuk cek phone tanpa acting on it. Urge biasanya berlalu dalam 5-10 menit.</p><h3>Tools yang Membantu</h3><ul><li><strong>App Blockers:</strong> Freedom, Forest, Cold Turkey (nama yang bagus untuk digital detox tool!)</li><li><strong>Grayscale Mode:</strong> Built-in di iOS dan Android</li><li><strong>Do Not Disturb:</strong> Schedule automatic DND times</li></ul><h3>Manfaat Mindfulness Digital</h3><ul><li>Increased focus dan productivity</li><li>Better sleep quality</li><li>Reduced anxiety dan stress</li><li>Deeper relationships</li><li>More presence dan joy</li></ul>',
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'view_count' => 234,
                'read_time_minutes' => 7,
                'seo_title' => 'Mindfulness di Era Digital: Tetap Fokus Ditengah Distraksi',
                'seo_description' => 'Strategi mindfulness untuk mengatasi distraksi digital dan menjaga kesehatan mental di era screen time tinggi.',
                'seo_keywords' => 'mindfulness, digital detox, screen time, mental health, focus',
                'tags' => [$tagWellness->id, $tagKesehatan->id]
            ],
            [
                'title' => 'Grief dan Loss: Memproses Kesedihan dengan Sehat',
                'slug' => 'grief-and-loss-kesedihan',
                'excerpt' => 'Kehilangan adalah bagian dari kehidupan, tapi cara kita memprosesnya penting untuk kesehatan mental. Pelajari tahap grief dan cara menghadapinya dengan sehat.',
                'content' => '<h2>Memahami Grief</h2><p>Grief adalah reaksi emosional alami terhadap kehilangan. Bukan hanya tentang kematian - kita mengalami grief untuk kehilangan pekerjaan, akhir hubungan, pindah, atau bahkan kehilangan versi identitas kita yang dulu.</p><h3>5 Tahap Grief (Kubler-Ross)</h3><h4>1. Denial (Penyangkalan)</h4><p><strong>Apa itu:</strong> "Ini tidak bisa terjadi pada saya" atau "Ini semua adalah kesalahan".</p><p><strong>Tujuannya:</strong> Penyangkalan adalah mekanisme protective. Memberikan sedikit waktu untuk memproses realitas yang berat.</p><h4>2. Anger (Kemarahan)</h4><p><strong>Apa itu:</strong> Ketika kita mulai menerima kenyataan, emosi bergeser ke kemarahan. "Mengapa ini terjadi pada saya?" atau "Ini tidak adil!"</p><p><strong>Penting:</strong> Mengekspresikan kemarahan dengan sehat penting. Jangan potong ini.</p><h4>3. Bargaining (Negosiasi)</h4><p><strong>Apa itu:</strong> Mencoba untuk menghindari realitas dengan "jika hanya" atau "bagaimana jika" thinking. "Jika saja saya..."</p><p><strong>Waspada terhadap:</strong> Guilt yang dapat berkembang pada tahap ini.</p><h4>4. Depression/Sadness (Kesedihan)</h4><p><strong>Apa itu:</strong> Penerimaan mulai datang, tapi dengan itu datang kesadaran mendalam tentang kehilangan. Ini adalah tahap di mana Anda benar-benar bersedih.</p><p><strong>Penting:</strong> Ini adalah bagian penting dari proses. Jangan coba "cepat-cepat" lewati ini.</p><h4>5. Acceptance (Penerimaan)</h4><p><strong>Apa itu:</strong> Bukan berarti OK dengan kehilangan, tapi mulai menerima realitas dan menemukan cara untuk hidup dengan itu.</p><p><strong>Catatan:</strong> Ini bukan tahap "berakhir". Grief adalah cyclical dan dapat kembali.</p><h3>Poin Penting tentang Model ini</h3><ul><li>Tidak semua orang melewati semua tahap</li><li>Tidak harus dalam urutan ini</li><li>Seseorang dapat bergerak maju dan mundur</li><li>Durasi berkisar dari minggu hingga tahun</li></ul><h3>Cara Sehat Memproses Grief</h3><h4>1. Izinkan Diri Merasakan</h4><p>Jangan menekan atau menghindari perasaan. Lepaskan apa yang muncul.</p><h4>2. Ekspresi Kreatif</h4><ul><li>Journaling tentang memori dan perasaan</li><li>Seni atau musik</li><li>Menulis surat ke orang yang hilang</li></ul><h4>3. Ritual Memorial</h4><ul><li>Ceremony yang bermakna</li><li>Menanam pohon atau menciptakan sesuatu dalam memori mereka</li><li>Anniversary observances</li></ul><h4>4. Cari Dukungan</h4><ul><li>Berbagi dengan teman dan keluarga</li><li>Grief support groups</li><li>Therapy atau counseling</li></ul><h4>5. Jadilah Patient dengan Diri Sendiri</h4><p>Grief tidak memiliki timeline. Beberapa hari Anda akan baik-baik saja, beberapa hari Anda akan kembali membara. Ini normal.</p><h4>6. Jaga Kesehatan Dasar</h4><ul><li>Makan makanan bergizi</li><li>Ubah tidur</li><li>Aktivitas fisik ringan</li><li>Avoid alcohol dan substance abuse</li></ul><h3>Red Flags untuk Complicated Grief</h3><p>Cari bantuan profesional jika:</p><ul><li>Intensity dari grief tidak berkurang setelah enam bulan</li><li>Anda tidak bisa berfungsi dalam kehidupan sehari-hari</li><li>Persistent suicidal thoughts</li><li>Isolasi total dari orang lain</li></ul>',
                'status' => 'published',
                'published_at' => now(),
                'view_count' => 145,
                'read_time_minutes' => 8,
                'seo_title' => 'Grief dan Loss: Memproses Kesedihan dengan Sehat | MindfulMe',
                'seo_description' => 'Pahami tahap-tahap grief dan pelajari cara sehat untuk memproses kehilangan dan kesedihan dalam hidup Anda.',
                'seo_keywords' => 'grief, loss, bereavement, kesedihan, kesehatan mental, coping',
                'tags' => [$tagKesehatan->id, $tagPsikologi->id]
            ]
        ];

        $createdCount = 0;
        foreach ($articles as $articleData) {
            try {
                $tags = $articleData['tags'];
                unset($articleData['tags']);

                $article = Article::create([
                    'id' => Str::uuid()->toString(),
                    'author_id' => $user->uuid,
                    'category_id' => $category->id,
                    'author_name' => $user->name,
                    'author_email' => $user->email,
                    'verification_status' => 'verified',
                    'verified_by' => $user->uuid,
                    'verified_at' => now(),
                    ...$articleData
                ]);

                // Attach tags
                if (!empty($tags)) {
                    $article->tags()->attach($tags);
                }

                $createdCount++;
                $this->command->line('✓ ' . $article->title);
            } catch (\Exception $e) {
                $this->command->error('✗ Error creating article: ' . $e->getMessage());
            }
        }

        $this->command->info("\n" . '━' . str_repeat('━', 78) . '━');
        $this->command->info("Successfully created $createdCount mental health articles!");
        $this->command->info('━' . str_repeat('━', 78) . '━');
    }
}
