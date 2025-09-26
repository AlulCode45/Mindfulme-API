<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessages extends Model
{
    /** @use HasFactory<\Database\Factories\ChatMessagesFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'chat_message_id';
    protected $keyType = 'string';
    protected $guarded = [''];

    protected static function booted(): void
    {
        static::creating(function (ChatMessages $chatMessage) {
            $chatMessage->chat_message_id = (string) \Illuminate\Support\Str::uuid();
        });
    }
}
