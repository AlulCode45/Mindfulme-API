<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BundlePackage extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'bundle_id';
    protected $keyType = 'string';
    protected $guarded = [''];

    protected static function booted(): void
    {
        static::creating(function (BundlePackage $bundlePackage) {
            $bundlePackage->bundle_id = (string) \Illuminate\Support\Str::uuid();
        });
    }
}
