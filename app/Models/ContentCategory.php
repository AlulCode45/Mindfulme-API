<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ContentCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'deleted_at',
    ];

    // Generate slug automatically
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relationships
    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    public function publishedArticles()
    {
        return $this->hasMany(Article::class, 'category_id')->published();
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'category_id');
    }

    public function publishedVideos()
    {
        return $this->hasMany(Video::class, 'category_id')->published();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
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
    public function isActive()
    {
        return $this->is_active;
    }
}