<?php

namespace App\Models;

use App\Enums\TestimonialApprovalStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonials extends Model
{
    /** @use HasFactory<\Database\Factories\TestimonialsFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'testimonial_id';
    protected $keyType = 'string';
    protected $guarded = [''];

    public $casts = [
        'status' => TestimonialApprovalStatus::class
    ];
    protected static function booted(): void
    {
        static::creating(function (Testimonials $testimonial) {
            $testimonial->testimonial_id = (string) \Illuminate\Support\Str::uuid();
        });
    }
}
