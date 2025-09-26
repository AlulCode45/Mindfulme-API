<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignments extends Model
{
    /** @use HasFactory<\Database\Factories\AssignmentsFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'assignment_id';
    protected $keyType = 'string';
    protected $guarded = [''];

    protected static function booted(): void
    {
        static::creating(function (Assignments $assignment) {
            $assignment->assignment_id = (string) \Illuminate\Support\Str::uuid();
        });
    }
}
