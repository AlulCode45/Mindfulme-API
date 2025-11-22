<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionType extends Model
{
    /** @use HasFactory<\Database\Factories\SessionTypeFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'session_type_id';
    protected $keyType = 'string';
    protected $guarded = [''];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'duration_minutes' => 'integer',
        'max_participants' => 'integer',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointments::class, 'session_type_id', 'session_type_id');
    }

    protected static function booted(): void
    {
        static::creating(function (SessionType $sessionType) {
            $sessionType->session_type_id = (string) \Illuminate\Support\Str::uuid();
        });
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDuration(): string
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice(): string
    {
        return 'Rp ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Get consultation type label
     */
    public function getConsultationTypeLabel(): string
    {
        return match ($this->consultation_type) {
            'individual' => 'Individual',
            'couples' => 'Couples',
            'family' => 'Family',
            'group' => 'Group',
            default => ucfirst($this->consultation_type)
        };
    }

    /**
     * Get scope for active session types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get scope for specific consultation type
     */
    public function scopeByConsultationType($query, string $type)
    {
        return $query->where('consultation_type', $type);
    }
}