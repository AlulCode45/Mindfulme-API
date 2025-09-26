<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{
    /** @use HasFactory<\Database\Factories\EvidenceFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'evidence_id';
    protected $keyType = 'string';
    protected $guarded = [''];

    protected static function booted(): void
    {
        static::creating(function (Evidence $evidence) {
            $evidence->evidence_id = (string) \Illuminate\Support\Str::uuid();
        });
    }
}
