<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointments extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentsFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'appointment_id';
    protected $keyType = 'string';
    protected $guarded = [''];

    public $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'status' => AppointmentStatus::class,
        'price' => 'decimal:2',
        'payment_paid_at' => 'datetime',
        'canceled_at' => 'datetime',
        'reminded_at' => 'datetime',
        'participants' => 'array',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaints::class, 'complaint_id', 'complaint_id');
    }

    public function psychologist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'psychologist_id', 'uuid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function sessionType(): BelongsTo
    {
        return $this->belongsTo(SessionType::class, 'session_type_id', 'session_type_id');
    }

    protected static function booted(): void
    {
        static::creating(function (Appointments $appointment) {
            $appointment->appointment_id = (string) \Illuminate\Support\Str::uuid();
        });

        static::saving(function (Appointments $appointment) {
            if ($appointment->session_type_id && !$appointment->end_time) {
                $sessionType = SessionType::find($appointment->session_type_id);
                if ($sessionType) {
                    $appointment->end_time = $appointment->start_time->copy()->addMinutes($sessionType->duration_minutes);
                }
            }
        });
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice(): string
    {
        $price = $this->price ?? 0;
        return 'Rp ' . number_format((float) $price, 2, ',', '.');
    }

    /**
     * Get session duration in minutes
     */
    public function getDurationInMinutes(): int
    {
        if ($this->sessionType) {
            return $this->sessionType->duration_minutes;
        }

        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }

        return 60; // Default 60 minutes
    }

    /**
     * Check if appointment can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [AppointmentStatus::SCHEDULED])
               && $this->start_time->diffInHours(now()) >= 24;
    }

    /**
     * Check if appointment can be rescheduled
     */
    public function canBeRescheduled(): bool
    {
        return in_array($this->status, [AppointmentStatus::SCHEDULED])
               && $this->start_time->diffInHours(now()) >= 24;
    }

    /**
     * Get status label with color
     */
    public function getStatusWithColor(): array
    {
        return match($this->status) {
            AppointmentStatus::SCHEDULED => ['label' => 'Scheduled', 'color' => 'blue'],
            AppointmentStatus::COMPLETED => ['label' => 'Completed', 'color' => 'green'],
            AppointmentStatus::CANCELED => ['label' => 'Canceled', 'color' => 'red'],
            default => ['label' => $this->status, 'color' => 'gray']
        };
    }

    /**
     * Scope for upcoming appointments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
                    ->whereIn('status', [AppointmentStatus::SCHEDULED]);
    }

    /**
     * Scope for past appointments
     */
    public function scopePast($query)
    {
        return $query->where('start_time', '<', now())
                    ->orWhereIn('status', [AppointmentStatus::COMPLETED, AppointmentStatus::CANCELED]);
    }

    /**
     * Scope for psychologist's appointments
     */
    public function scopeForPsychologist($query, string $psychologistId)
    {
        return $query->where('psychologist_id', $psychologistId);
    }

    /**
     * Scope for user's appointments
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }
}
