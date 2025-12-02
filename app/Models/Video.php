<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Video extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'video_url',
        'thumbnail_url',
        'duration_seconds',
        'author_id',
        'category_id',
        'status',
        'published_at',
        'view_count',
        'transcript',
        'captions_url',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'duration_seconds' => 'integer',
    ];

    protected $dates = [
        'published_at',
        'deleted_at',
    ];

    // Generate slug automatically
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($video) {
            if (empty($video->slug)) {
                $video->slug = Str::slug($video->title);
            }
        });

        static::updating(function ($video) {
            if ($video->isDirty('title') && empty($video->slug)) {
                $video->slug = Str::slug($video->title);
            }
        });
    }

    // Relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(ContentTag::class, 'video_tag', 'video_id', 'tag_id');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeByCategory($query, $categorySlug)
    {
        return $query->whereHas('category', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    public function scopeByTag($query, $tagSlug)
    {
        return $query->whereHas('tags', function ($q) use ($tagSlug) {
            $q->where('slug', $tagSlug);
        });
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    // Accessors
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_url) {
            return url('storage/' . $this->thumbnail_url);
        }

        // Generate default thumbnail based on category
        return "https://via.placeholder.com/640x360/4F46E5/FFFFFF?text=" . urlencode($this->title);
    }

    public function getFormattedDurationAttribute()
    {
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getAuthorNameAttribute()
    {
        return $this->author ? $this->author->name : 'Unknown Author';
    }

    public function getAuthorPhotoAttribute()
    {
        return $this->author ? $this->author->profile_photo : null;
    }

    public function getAuthorBioAttribute()
    {
        return $this->author ? $this->author->bio : null;
    }

    // Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function isPublished()
    {
        return $this->status === 'published' && $this->published_at && $this->published_at->isPast();
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
            default => $this->status,
        };
    }

    public function getVideoEmbedUrl()
    {
        // Handle YouTube URLs
        if (str_contains($this->video_url, 'youtube.com') || str_contains($this->video_url, 'youtu.be')) {
            $videoId = $this->extractYouTubeVideoId($this->video_url);
            return "https://www.youtube.com/embed/{$videoId}";
        }

        // Handle Vimeo URLs
        if (str_contains($this->video_url, 'vimeo.com')) {
            $videoId = $this->extractVimeoVideoId($this->video_url);
            return "https://player.vimeo.com/video/{$videoId}";
        }

        // Return original URL for other platforms
        return $this->video_url;
    }

    private function extractYouTubeVideoId($url)
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
        preg_match($pattern, $url, $matches);

        return $matches[1] ?? null;
    }

    private function extractVimeoVideoId($url)
    {
        $pattern = '/(?:vimeo\.com\/)(\d+)(?:$|\/)/';
        preg_match($pattern, $url, $matches);

        return $matches[1] ?? null;
    }
}