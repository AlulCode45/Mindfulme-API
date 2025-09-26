<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'user_detail_id';
    protected $keyType = 'string';
    protected $guarded = [''];
    public $timestamps = false;


    protected static function booted(): void
    {
        static::creating(function (UserDetail $userDetail) {
            $userDetail->user_detail_id = (string) \Illuminate\Support\Str::uuid();
        });
    }
}
