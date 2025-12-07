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

        // Transform articles to include author info for volunteer submissions
        $transformedArticles = $articles->getCollection()->map(function ($article) {
            $data = $article->toArray();
            $data['author_name'] = $article->author_name;
            $data['author_email'] = $article->author_email;
            $data['author_phone'] = $article->author_phone;

            // Create author object with info
            if ($article->author) {
                $data['author'] = [
                    'name' => $article->author->name,
                    'email' => $article->author->email
                ];
            } elseif ($article->type === 'volunteer_submission') {
                // For volunteer submissions, create author object from fields
                $data['author'] = [
                    'name' => $article->author_name,
                    'email' => $article->author_email
                ];
            }

            return $data;
        });

        return response()->json([
            'success' => true,
            'message' => 'Articles retrieved successfully',
            'data' => $transformedArticles,
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

        $data = $article->toArray();
        $data['author_name'] = $article->author_name;
        $data['author_email'] = $article->author_email;
        $data['author_phone'] = $article->author_phone;

        // Create author object with info
        if ($article->author) {
            $data['author'] = [
                'name' => $article->author->name,
                'email' => $article->author->email
            ];
        } elseif ($article->type === 'volunteer_submission') {
            // For volunteer submissions, create author object from fields
            $data['author'] = [
                'name' => $article->author_name,
                'email' => $article->author_email
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Article retrieved successfully',
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        // Handle tag_ids specially - it can come as JSON string or array
        $tagIds = $request->input('tag_ids');
        if (is_string($tagIds)) {
            $tagIds = json_decode($tagIds, true) ?? [];
        }
        if (!is_array($tagIds)) {
            $tagIds = [];
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'category_id' => 'required|string|exists:content_categories,id',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'in:draft,published,archived',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:255',
        ]);

        // Manually add tag_ids to validated data
        $validated['tag_ids'] = $tagIds;

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

        // Generate UUID for the article
        $validated['id'] = Str::uuid()->toString();

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
        // Handle tag_ids specially - it can come as JSON string or array
        $tagIds = $request->input('tag_ids');
        if (is_string($tagIds)) {
            $tagIds = json_decode($tagIds, true) ?? [];
        }
        if (!is_array($tagIds)) {
            $tagIds = [];
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'excerpt' => 'sometimes|string|max:500',
            'content' => 'sometimes|string',
            'category_id' => 'sometimes|string|exists:content_categories,id',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'sometimes|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'seo_title' => 'sometimes|string|max:255',
            'seo_description' => 'sometimes|string|max:500',
            'seo_keywords' => 'sometimes|string|max:255',
        ]);

        // Manually add tag_ids to validated data
        $validated['tag_ids'] = $tagIds;

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

    /**
     * Handle volunteer news article submission
     */
    public function volunteerSubmit(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:10|max:255',
            'excerpt' => 'required|string|min:20|max:500',
            'content' => 'required|string|min:100',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|in:volunteer_submission',
        ]);

        // Get the authenticated user
        $user = $request->user();
        if (!$user || $user->role !== 'volunteer') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only volunteers can submit articles.',
            ], 403);
        }

        // Get author info from authenticated user
        $validated['author_name'] = $user->name;
        $validated['author_email'] = $user->email;
        $validated['author_phone'] = $user->phone ?? '';

        // Get the news category ID first
        $newsCategory = ContentCategory::where('slug', 'news')->first();
        if (!$newsCategory) {
            return response()->json([
                'success' => false,
                'message' => 'News category not found',
            ], 404);
        }

        try {
            // Generate unique slug
          $slug = Str::slug($validated['title']);
          $originalSlug = $slug;
          $counter = 1;

          // Ensure slug is unique
          while (Article::where('slug', $slug)->exists()) {
              $slug = $originalSlug . '-' . $counter;
              $counter++;
          }

          $articleData = [
                'id' => Str::uuid()->toString(),
                'title' => $validated['title'],
                'slug' => $slug,
                'excerpt' => $validated['excerpt'],
                'content' => $validated['content'],
                'author_name' => $validated['author_name'],
                'author_email' => $validated['author_email'],
                'author_phone' => $validated['author_phone'],
                'type' => 'volunteer_submission',
                'category_id' => $newsCategory->id,
                'status' => 'draft',
                'verification_status' => 'pending',
                'verification_notes' => 'Submitted by volunteer. Waiting for admin review.',
                'view_count' => 0,
                'read_time_minutes' => ceil(str_word_count(strip_tags($validated['content'])) / 200),
            ];

            // Handle featured image upload
            if ($request->hasFile('featured_image')) {
                $image = $request->file('featured_image');
                $imagePath = $image->store('articles/images', 'public');
                $articleData['featured_image'] = $imagePath;
            }

            $article = Article::create($articleData);

            return response()->json([
                'success' => true,
                'message' => 'Article submitted successfully. Your article is now under review by our admin team.',
                'data' => $article->load(['category']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit article: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get articles pending verification
     */
    public function pendingVerification(Request $request)
    {
        $query = Article::with(['author', 'category'])
            ->pending()
            ->orderBy('created_at', 'desc');

        // Pagination
        $limit = min($request->input('limit', 10), 50);
        $articles = $query->paginate($limit);

        // Transform articles to include author info for volunteer submissions
        $transformedArticles = $articles->getCollection()->map(function ($article) {
            $data = $article->toArray();
            $data['author_name'] = $article->author_name;
            $data['author_email'] = $article->author_email;
            $data['author_phone'] = $article->author_phone;

            // Create author object with info
            if ($article->author) {
                $data['author'] = [
                    'name' => $article->author->name,
                    'email' => $article->author->email
                ];
            } elseif ($article->type === 'volunteer_submission') {
                // For volunteer submissions, create author object from fields
                $data['author'] = [
                    'name' => $article->author_name,
                    'email' => $article->author_email
                ];
            }

            return $data;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pending articles retrieved successfully',
            'data' => $transformedArticles,
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    /**
     * Approve article
     */
    public function approve(Request $request, Article $article)
    {
        $validated = $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $article->update([
                'verification_status' => 'approved',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'verification_notes' => $validated['verification_notes'] ?? 'Article approved and published.',
                'status' => 'published',
                'published_at' => now(),
            ]);

            $article->load(['author', 'category', 'verifier']);
            $data = $article->toArray();
            $data['author_name'] = $article->author_name;
            $data['author_email'] = $article->author_email;
            $data['author_phone'] = $article->author_phone;

            // Create author object with info
            if ($article->author) {
                $data['author'] = [
                    'name' => $article->author->name,
                    'email' => $article->author->email
                ];
            } elseif ($article->type === 'volunteer_submission') {
                // For volunteer submissions, create author object from fields
                $data['author'] = [
                    'name' => $article->author_name,
                    'email' => $article->author_email
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Article approved successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve article: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject article
     */
    public function reject(Request $request, Article $article)
    {
        $validated = $request->validate([
            'verification_notes' => 'required|string|max:1000',
        ]);

        try {
            $article->update([
                'verification_status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'verification_notes' => $validated['verification_notes'],
                'status' => 'draft',
            ]);

            $article->load(['author', 'category', 'verifier']);
            $data = $article->toArray();
            $data['author_name'] = $article->author_name;
            $data['author_email'] = $article->author_email;
            $data['author_phone'] = $article->author_phone;

            // Create author object with info
            if ($article->author) {
                $data['author'] = [
                    'name' => $article->author->name,
                    'email' => $article->author->email
                ];
            } elseif ($article->type === 'volunteer_submission') {
                // For volunteer submissions, create author object from fields
                $data['author'] = [
                    'name' => $article->author_name,
                    'email' => $article->author_email
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Article rejected successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject article: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get volunteer's articles
     */
    public function getVolunteerArticles(Request $request)
    {
        try {
            // Get the authenticated user from Sanctum
            $user = $request->user();

            // Check if user exists and is a volunteer
            if (!$user || $user->role !== 'volunteer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only volunteers can access this endpoint.',
                    'data' => []
                ], 403);
            }

            // Get articles submitted by this volunteer user
            // Match by author_email in the articles table
            $articles = Article::where('type', 'volunteer_submission')
                ->where('author_email', $user->email)
                ->orderBy('created_at', 'desc')
                ->get(['id', 'title', 'excerpt', 'verification_status', 'verification_notes', 'created_at', 'view_count']);

            return response()->json([
                'success' => true,
                'message' => 'Volunteer articles retrieved successfully',
                'data' => $articles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get volunteer articles: ' . $e->getMessage(),
            ], 500);
        }
    }
}