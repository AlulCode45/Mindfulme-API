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
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    protected $guarded = [''];
    public $timestamps = false;

    protected $casts = [
        'status' => ComplaintStatus::class,
    ];
}
