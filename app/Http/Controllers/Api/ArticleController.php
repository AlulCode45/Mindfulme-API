<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ContentCategory;
use App\Models\ContentTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with(['author', 'category', 'tags']);

        // Apply filters
        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'published') {
                $query->published();
            } elseif ($status === 'draft') {
                $query->draft();
            } elseif ($status === 'archived') {
                $query->archived();
            }
        }

        if ($request->has('category')) {
            $query->byCategory($request->input('category'));
        }

        if ($request->has('tag')) {
            $query->byTag($request->input('tag'));
        }

        if ($request->has('search')) {
            $query->search($request->input('search'));
        }

        if ($request->has('author')) {
            $query->where('author_id', $request->input('author'));
        }

        // Ordering
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'popular':
                $query->orderBy('view_count', 'desc');
                break;
            case 'oldest':
                $query->orderBy('published_at', 'asc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            default: // latest
                $query->orderBy('published_at', 'desc');
        }

        // Pagination
        $limit = min($request->input('limit', 10), 50);
        $articles = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => 'Articles retrieved successfully',
            'data' => $articles->items(),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function show(Article $article)
    {
        $article->load(['author', 'category', 'tags']);

        return response()->json([
            'success' => true,
            'message' => 'Article retrieved successfully',
            'data' => $article,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'category_id' => 'required|exists:content_categories,id',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:content_tags,id',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'enum:draft,published,archived',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:255',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $imagePath = $image->store('articles/images', 'public');
            $validated['featured_image'] = $imagePath;
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        } else {
            // Ensure slug is unique
            $validated['slug'] = Str::slug($validated['slug']);
            while (Article::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] .= '-' . time();
            }
        }

        $validated['author_id'] = auth()->id();

        $article = Article::create($validated);

        // Attach tags
        if (!empty($validated['tag_ids'])) {
            $article->tags()->attach($validated['tag_ids']);
        }

        // Calculate read time
        if (!$article->read_time_minutes) {
            $wordCount = str_word_count(strip_tags($article->content));
            $article->read_time_minutes = ceil($wordCount / 200);
            $article->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Article created successfully',
            'data' => $article->load(['author', 'category', 'tags']),
        ], 201);
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'excerpt' => 'sometimes|string|max:500',
            'content' => 'sometimes|string',
            'category_id' => 'sometimes|exists:content_categories,id',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:content_tags,id',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'sometimes|enum:draft,published,archived',
            'published_at' => 'nullable|date',
            'seo_title' => 'sometimes|string|max:255',
            'seo_description' => 'sometimes|string|max:500',
            'seo_keywords' => 'sometimes|string|max:255',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($article->featured_image) {
                Storage::disk('public')->delete($article->featured_image);
            }

            $image = $request->file('featured_image');
            $imagePath = $image->store('articles/images', 'public');
            $validated['featured_image'] = $imagePath;
        }

        // Generate slug if title changed and slug not provided
        if (isset($validated['title']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            // Ensure slug is unique
            while (Article::where('slug', $validated['slug'])->where('id', '!=', $article->id)->exists()) {
                $validated['slug'] .= '-' . time();
            }
        } elseif (isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['slug']);
            // Ensure slug is unique
            while (Article::where('slug', $validated['slug'])->where('id', '!=', $article->id)->exists()) {
                $validated['slug'] .= '-' . time();
            }
        }

        $article->update($validated);

        // Sync tags
        if (isset($validated['tag_ids'])) {
            $article->tags()->sync($validated['tag_ids']);
        }

        // Recalculate read time if content changed
        if (isset($validated['content'])) {
            $wordCount = str_word_count(strip_tags($article->content));
            $article->read_time_minutes = ceil($wordCount / 200);
            $article->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Article updated successfully',
            'data' => $article->load(['author', 'category', 'tags']),
        ]);
    }

    public function destroy(Article $article)
    {
        // Delete featured image if exists
        if ($article->featured_image) {
            Storage::disk('public')->delete($article->featured_image);
        }

        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Article deleted successfully',
        ]);
    }

    public function trackView(Article $article)
    {
        $article->incrementViewCount();

        return response()->json([
            'success' => true,
            'message' => 'View tracked successfully',
            'view_count' => $article->view_count,
        ]);
    }

    public function getBySlug($slug)
    {
        $article = Article::with(['author', 'category', 'tags'])
            ->where('slug', $slug)
            ->published()
            ->first();

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
            ], 404);
        }

        // Track view
        $article->incrementViewCount();

        return response()->json([
            'success' => true,
            'message' => 'Article retrieved successfully',
            'data' => $article,
        ]);
    }
}