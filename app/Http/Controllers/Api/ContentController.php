<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Video;
use App\Models\ContentCategory;
use App\Models\ContentTag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    public function stats()
    {
        $totalArticles = Article::count();
        $publishedArticles = Article::published()->count();
        $totalVideos = Video::count();
        $publishedVideos = Video::published()->count();

        // Calculate total views
        $totalViews = Article::sum('view_count') + Video::sum('view_count');

        // Calculate total read time (estimated)
        $totalReadTime = Article::sum('read_time_minutes');

        // Get top categories
        $articleCategories = Article::join('content_categories', 'articles.category_id', '=', 'content_categories.id')
            ->select('content_categories.name', DB::raw('count(*) as count'))
            ->groupBy('content_categories.id', 'content_categories.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $videoCategories = Video::join('content_categories', 'videos.category_id', '=', 'content_categories.id')
            ->select('content_categories.name', DB::raw('count(*) as count'))
            ->groupBy('content_categories.id', 'content_categories.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $topCategories = $articleCategories->merge($videoCategories)
            ->groupBy('name')
            ->map(function ($item) {
                return [
                    'category' => $item->name,
                    'count' => collect($item)->sum('count')
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->take(6);

        // Get recent activity
        $recentArticles = Article::with(['author'])
            ->published()
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get(['title', 'published_at', 'view_count', 'created_at']);

        $recentVideos = Video::with(['author'])
            ->published()
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get(['title', 'published_at', 'view_count', 'created_at']);

        $recentActivity = $recentArticles->concat($recentVideos)
            ->map(function ($item) {
                $type = ($item instanceof \App\Models\Article) ? 'article' : 'video';
                return [
                    'type' => $type,
                    'title' => $item->title,
                    'date' => $item->published_at ? $item->published_at->format('Y-m-d') : $item->created_at->format('Y-m-d'),
                    'views' => $item->view_count,
                ];
            })
            ->sortByDesc('date')
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Content statistics retrieved successfully',
            'data' => [
                'total_articles' => $totalArticles,
                'published_articles' => $publishedArticles,
                'total_videos' => $totalVideos,
                'published_videos' => $publishedVideos,
                'total_views' => $totalViews,
                'total_read_time' => $totalReadTime,
                'top_categories' => $topCategories->toArray(),
                'recent_activity' => $recentActivity->toArray(),
            ],
        ]);
    }

    public function categories()
    {
        $categories = ContentCategory::active()
            ->withCount(['publishedArticles', 'publishedVideos'])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ]);
    }

    public function tags()
    {
        $tags = ContentTag::withCount(['publishedArticles', 'publishedVideos'])
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Tags retrieved successfully',
            'data' => $tags,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $type = $request->input('type', 'all'); // all, articles, videos
        $category = $request->input('category');
        $limit = min($request->input('limit', 20), 50);

        $results = [];

        if ($type === 'all' || $type === 'articles') {
            $articlesQuery = Article::published()
                ->with(['author', 'category', 'tags'])
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('excerpt', 'LIKE', "%{$query}%");
                });

            if ($category) {
                $articlesQuery->byCategory($category);
            }

            $articles = $articlesQuery->limit($limit)->get();
            $results['articles'] = $articles;
        }

        if ($type === 'all' || $type === 'videos') {
            $videosQuery = Video::published()
                ->with(['author', 'category', 'tags'])
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                });

            if ($category) {
                $videosQuery->byCategory($category);
            }

            $videos = $videosQuery->limit($limit)->get();
            $results['videos'] = $videos;
        }

        return response()->json([
            'success' => true,
            'message' => 'Search results retrieved successfully',
            'data' => $results,
        ]);
    }
}