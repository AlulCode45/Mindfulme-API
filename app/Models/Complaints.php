<?php

namespace App\Models;

use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaints extends Model
{
    /** @use HasFactory<\Database\Factories\ComplaintsFactory> */
    use HasUuids, HasFactory;

    public $incrementing = false;
    protected $primaryKey = 'complaint_id';
    protected $keyType = 'string';
    protected $guarded = [''];
    public $timestamps = false;

    protected $casts = [
        'status' => ComplaintStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Complaints $complaint) {
            $complaint->complaint_id = (string) \Illuminate\Support\Str::uuid();
        });
    }
}
