<?php

namespace App\Models;

use App\Enums\TestimonialApprovalStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
     * Ensure media_url is always an absolute URL pointing to the correct APP_URL.
     */
    protected function mediaUrl(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if (!$value)
                    return null;

                $appUrl = rtrim(config('app.url'), '/');

                // Already absolute URL — fix domain if stored with wrong base (e.g. localhost)
                if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                    // Replace any host (including localhost) with actual APP_URL if path contains /storage/
                    if (str_contains($value, '/storage/')) {
                        $path = '/storage/' . explode('/storage/', $value, 2)[1];
                        return $appUrl . $path;
                    }
                    return $value;
                }

                // Relative path — prepend APP_URL
                return $appUrl . '/' . ltrim($value, '/');
            }
        );
    }

    /**
     * Get the user that owns the testimonial.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
