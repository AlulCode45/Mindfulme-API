<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasUuids;

    protected $primaryKey = 'review_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'psychologist_id',
        'user_id',
        'appointment_id',
        'rating',
        'review_text',
        'is_anonymous',
        'is_verified',
        'response',
        'response_date'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_anonymous' => 'boolean',
        'is_verified' => 'boolean',
        'response_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the psychologist that owns the review.
     */
    public function psychologist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'psychologist_id', 'uuid');
    }

    /**
     * Get the user that owns the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    /**
     * Get the appointment that owns the review.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointments::class, 'appointment_id', 'appointment_id');
    }

    /**
     * Scope a query to only include verified reviews.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include non-anonymous reviews.
     */
    public function scopeNotAnonymous($query)
    {
        return $query->where('is_anonymous', false);
    }

    /**
     * Scope a query to get reviews by psychologist.
     */
    public function scopeForPsychologist($query, $psychologistId)
    {
        return $query->where('psychologist_id', $psychologistId);
    }

    /**
     * Scope a query to get reviews by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the review text with proper fallback.
     */
    public function getDisplayReviewTextAttribute()
    {
        return $this->review_text ?: 'Pengguna tidak memberikan ulasan tertulis.';
    }

    /**
     * Get the user's display name (handles anonymous reviews).
     */
    public function getDisplayUserAttribute()
    {
        if ($this->is_anonymous) {
            return 'Pengguna Anonim';
        }

        return $this->user ? $this->user->name : 'Pengguna Tidak Diketahui';
    }

    /**
     * Get rating in stars format.
     */
    public function getStarsAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Check if the authenticated user can modify this review.
     */
    public function canBeModifiedBy($user)
    {
        if (!$user) {
            return false;
        }

        // User can modify their own review
        if ($this->user_id === $user->uuid) {
            return true;
        }

        // Psychologist can respond to reviews about them
        if ($this->psychologist_id === $user->uuid) {
            return true;
        }

        // Admin can modify any review
        return $user->hasRole('superadmin');
    }
}