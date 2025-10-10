<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AiDiscussion extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'ai_discussion_id';
    protected $keyType = 'string';
    protected $guarded = [''];

    protected static function booted(): void
    {
        static::creating(function (AiDiscussion $aiDiscussion) {
            $aiDiscussion->ai_discussion_id = (string) \Illuminate\Support\Str::uuid();
        });
    }
}
