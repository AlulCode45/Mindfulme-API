<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_id',
        'category_id',
        'status',
        'published_at',
        'view_count',
        'read_time_minutes',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'read_time_minutes' => 'integer',
    ];

    protected $dates = [
        'published_at',
        'deleted_at',
    ];

    // Generate slug automatically
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });

        static::updating(function ($article) {
            if ($article->isDirty('title') && empty($article->slug)) {
                $article->slug = Str::slug($article->title);
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
        return $this->belongsToMany(ContentTag::class, 'article_tag', 'article_id', 'tag_id');
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
              ->orWhere('excerpt', 'LIKE', "%{$term}%")
              ->orWhere('content', 'LIKE', "%{$term}%");
        });
    }

    // Accessors
    public function getReadTimeAttribute($value)
    {
        // Calculate read time based on content length if not set
        if (!$value && $this->content) {
            $wordCount = str_word_count(strip_tags($this->content));
            $value = ceil($wordCount / 200); // Average reading speed: 200 words per minute
        }

        return $value;
    }

    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return url('storage/' . $this->featured_image);
        }

        return null;
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
}