<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\ContentCategory;
use App\Models\ContentTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $query = Video::with(['author', 'category', 'tags']);

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
            case 'duration':
                $query->orderBy('duration_seconds', 'asc');
                break;
            default: // latest
                $query->orderBy('published_at', 'desc');
        }

        // Pagination
        $limit = min($request->input('limit', 10), 50);
        $videos = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => 'Videos retrieved successfully',
            'data' => $videos->items(),
            'meta' => [
                'current_page' => $videos->currentPage(),
                'last_page' => $videos->lastPage(),
                'per_page' => $videos->perPage(),
                'total' => $videos->total(),
            ],
        ]);
    }

    public function show(Video $video)
    {
        $video->load(['author', 'category', 'tags']);

        return response()->json([
            'success' => true,
            'message' => 'Video retrieved successfully',
            'data' => $video,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'video_url' => 'required|url',
            'thumbnail_url' => 'nullable|url',
            'duration_seconds' => 'nullable|integer|min:1',
            'category_id' => 'required|exists:content_categories,id',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:content_tags,id',
            'status' => 'in:draft,published,archived',
            'published_at' => 'nullable|date',
            'transcript' => 'nullable|string',
            'captions_url' => 'nullable|url',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        } else {
            // Ensure slug is unique
            $validated['slug'] = Str::slug($validated['slug']);
            while (Video::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] .= '-' . time();
            }
        }

        $validated['author_id'] = auth()->id();

        // Extract duration from YouTube URL if not provided
        if (!isset($validated['duration_seconds']) && str_contains($validated['video_url'], 'youtube.com')) {
            $duration = $this->extractYouTubeDuration($validated['video_url']);
            if ($duration) {
                $validated['duration_seconds'] = $duration;
            }
        }

        $video = Video::create($validated);

        // Attach tags
        if (!empty($validated['tag_ids'])) {
            $video->tags()->attach($validated['tag_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Video created successfully',
            'data' => $video->load(['author', 'category', 'tags']),
        ], 201);
    }

    public function update(Request $request, Video $video)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'video_url' => 'sometimes|url',
            'thumbnail_url' => 'sometimes|url',
            'duration_seconds' => 'sometimes|integer|min:1',
            'category_id' => 'sometimes|exists:content_categories,id',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:content_tags,id',
            'status' => 'sometimes|in:draft,published,archived',
            'published_at' => 'sometimes|date',
            'transcript' => 'sometimes|string',
            'captions_url' => 'sometimes|url',
        ]);

        // Generate slug if title changed and slug not provided
        if (isset($validated['title']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            // Ensure slug is unique
            while (Video::where('slug', $validated['slug'])->where('id', '!=', $video->id)->exists()) {
                $validated['slug'] .= '-' . time();
            }
        } elseif (isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['slug']);
            // Ensure slug is unique
            while (Video::where('slug', $validated['slug'])->where('id', '!=', $video->id)->exists()) {
                $validated['slug'] .= '-' . time();
            }
        }

        $video->update($validated);

        // Sync tags
        if (isset($validated['tag_ids'])) {
            $video->tags()->sync($validated['tag_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Video updated successfully',
            'data' => $video->load(['author', 'category', 'tags']),
        ]);
    }

    public function destroy(Video $video)
    {
        $video->delete();

        return response()->json([
            'success' => true,
            'message' => 'Video deleted successfully',
        ]);
    }

    public function trackView(Video $video)
    {
        $video->incrementViewCount();

        return response()->json([
            'success' => true,
            'message' => 'View tracked successfully',
            'view_count' => $video->view_count,
        ]);
    }

    public function getBySlug($slug)
    {
        $video = Video::with(['author', 'category', 'tags'])
            ->where('slug', $slug)
            ->published()
            ->first();

        if (!$video) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found',
            ], 404);
        }

        // Track view
        $video->incrementViewCount();

        return response()->json([
            'success' => true,
            'message' => 'Video retrieved successfully',
            'data' => $video,
        ]);
    }

    private function extractYouTubeDuration($url)
    {
        // Extract video ID from URL
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
        preg_match($pattern, $url, $matches);

        if (empty($matches[1])) {
            return null;
        }

        $videoId = $matches[1];

        // Get video metadata from YouTube API
        try {
            $apiKey = config('services.youtube.api_key');
            if (!$apiKey) {
                return null;
            }

            $response = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id={$videoId}&key={$apiKey}");

            if ($response) {
                $data = json_decode($response, true);
                if (!empty($data['items'][0]['contentDetails']['duration'])) {
                    $duration = $data['items'][0]['contentDetails']['duration'];
                    return $this->parseYouTubeDuration($duration);
                }
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return null;
    }

    private function parseYouTubeDuration($duration)
    {
        // Convert ISO 8601 duration to seconds
        $interval = new \DateInterval($duration);
        $seconds = 0;

        if ($interval->h) {
            $seconds += $interval->h * 3600;
        }
        if ($interval->i) {
            $seconds += $interval->i * 60;
        }
        if ($interval->s) {
            $seconds += $interval->s;
        }

        return $seconds;
    }
}