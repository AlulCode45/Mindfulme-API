<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\ContentCategory;

class NewsArticleCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Artikel',
                'slug' => 'artikel',
                'description' => 'Artikel informatif dan analisis mendalam',
                'color' => 'blue',
                'icon' => '📝',
                'is_active' => true,
            ],
            [
                'name' => 'Berita',
                'slug' => 'berita',
                'description' => 'Berita terbaru dan update penting',
                'color' => 'red',
                'icon' => '📰',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            $exists = ContentCategory::where('slug', $category['slug'])->first();

            if (!$exists) {
                ContentCategory::create([
                    'id' => Str::uuid()->toString(),
                    ...$category,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->info("✅ Category '{$category['name']}' created successfully");
            } else {
                $this->command->info("ℹ️ Category '{$category['name']}' already exists");
            }
        }
    }
}
