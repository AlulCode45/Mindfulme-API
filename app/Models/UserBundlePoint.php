<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserBundlePoint extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'user_bundle_id';
    protected $keyType = 'string';
    protected $guarded = [''];

    protected static function booted(): void
    {
        static::creating(function (UserBundlePoint $userBundlePoint) {
            $userBundlePoint->user_bundle_id = (string) \Illuminate\Support\Str::uuid();
        });
    }
}
