<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\ContentCategory;

class NewsCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if news category already exists
        $existingCategory = ContentCategory::where('slug', 'news')->first();

        if (!$existingCategory) {
            // Create news category
            ContentCategory::create([
                'id' => Str::uuid()->toString(),
                'name' => 'News',
                'slug' => 'news',
                'description' => 'News articles and announcements',
                'color' => '#3B82F6', // Blue color
                'icon' => 'newspaper',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✅ News category created successfully');
        } else {
            $this->command->info('ℹ️ News category already exists');
        }
    }
}