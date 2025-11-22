<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsychologistAvailability extends Model
{
    /** @use HasFactory<\Database\Factories\PsychologistAvailabilityFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'availability_id';
    protected $keyType = 'string';
    protected $guarded = [''];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_available' => 'boolean',
        'break_periods' => 'array',
    ];

    public function psychologist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'psychologist_id', 'uuid');
    }

    protected static function booted(): void
    {
        static::creating(function (PsychologistAvailability $availability) {
            $availability->availability_id = (string) \Illuminate\Support\Str::uuid();
        });
    }

    /**
     * Check if availability is currently active based on effective dates
     */
    public function isCurrentlyActive(): bool
    {
        $today = now()->toDateString();

        if ($this->effective_from && $today < $this->effective_from) {
            return false;
        }

        if ($this->effective_to && $today > $this->effective_to) {
            return false;
        }

        return $this->is_available;
    }

    /**
     * Get the day of week in a consistent format
     */
    public function getDayOfWeekFormatted(): string
    {
        return ucfirst($this->day_of_week);
    }

    /**
     * Get duration in minutes
     */
    public function getDurationInMinutes(): int
    {
        $start = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
        $end = \Carbon\Carbon::createFromFormat('H:i', $this->end_time);

        return $start->diffInMinutes($end);
    }
}