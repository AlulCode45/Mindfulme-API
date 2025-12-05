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
        'approval_status' => TestimonialApprovalStatus::class,
        'anonymous' => 'boolean',
        'rating' => 'integer'
    ];

    protected $with = ['user'];

    protected static function booted(): void
    {
        static::creating(function (Testimonials $testimonial) {
            $testimonial->testimonial_id = (string) \Illuminate\Support\Str::uuid();
        });
    }

    /**
     * Get the user that owns the testimonial.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
