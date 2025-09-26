<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Articles extends Model
{
    /** @use HasFactory<\Database\Factories\ArticlesFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'article_id';
    protected $keyType = 'string';
    protected $guarded = [''];

    protected static function booted(): void
    {
        static::creating(function (Articles $article) {
            $article->article_id = (string) \Illuminate\Support\Str::uuid();
        });
    }
}
