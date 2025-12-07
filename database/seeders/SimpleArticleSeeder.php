<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ContentCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SimpleArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the news category
        $newsCategory = ContentCategory::where('slug', 'news')->first();
        if (!$newsCategory) {
            $this->command->error('News category not found.');
            return;
        }

        // Get the user
        $user = User::first();
        if (!$user) {
            $this->command->error('No user found.');
            return;
        }

        // Create a simple test article
        try {
            $article = Article::create([
                'id' => Str::uuid()->toString(),
                'title' => 'Test Article - Welcome to MindfulMe',
                'slug' => 'test-article-welcome-to-mindfulme',
                'excerpt' => 'This is a test article for the MindfulMe news management system.',
                'content' => '<h2>Welcome to MindfulMe</h2><p>This is a test article created to demonstrate the news management system.</p><p>The WYSIWYG editor is working perfectly!</p>',
                'author_id' => $user->uuid,
                'category_id' => $newsCategory->id,
                'status' => 'published',
                'published_at' => now(),
                'view_count' => 0,
                'read_time_minutes' => 2,
                'seo_title' => 'Test Article | MindfulMe',
                'seo_description' => 'A test article for the MindfulMe platform.',
                'seo_keywords' => 'test, mindfulme, news'
            ]);

            $this->command->info('✅ Test article created successfully!');
            $this->command->line('Title: ' . $article->title);
            $this->command->line('Category ID: ' . $article->category_id);
            $this->command->line('Author ID: ' . $article->author_id);

        } catch (\Exception $e) {
            $this->command->error('Failed to create test article: ' . $e->getMessage());
        }
    }
}