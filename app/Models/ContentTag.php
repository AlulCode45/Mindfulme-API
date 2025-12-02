<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ContentTag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    protected $dates = [
        'deleted_at',
    ];

    // Generate slug automatically
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    // Relationships
    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_tag', 'tag_id', 'article_id');
    }

    public function publishedArticles()
    {
        return $this->belongsToMany(Article::class, 'article_tag', 'tag_id', 'article_id')->published();
    }

    public function videos()
    {
        return $this->belongsToMany(Video::class, 'video_tag', 'tag_id', 'video_id');
    }

    public function publishedVideos()
    {
        return $this->belongsToMany(Video::class, 'video_tag', 'tag_id', 'video_id')->published();
    }

    // Accessors
    public function getArticleCountAttribute()
    {
        return $this->publishedArticles()->count();
    }

    public function getVideoCountAttribute()
    {
        return $this->publishedVideos()->count();
    }

    public function getTotalContentAttribute()
    {
        return $this->article_count + $this->video_count;
    }

    // Methods
    public function getUrlAttribute()
    {
        return route('content.by-tag', $this->slug);
    }

    public function getStyleAttribute()
    {
        return [
            'backgroundColor' => $this->color ?? '#6b7280',
            'color' => '#ffffff'
        ];
    }
}