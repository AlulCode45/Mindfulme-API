<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuids, CanResetPassword, HasApiTokens;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'address',
        'motivation',
        'volunteer_status',
        'volunteer_notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function detail(): HasOne
    {
        return $this->hasOne(UserDetail::class, 'user_id', 'uuid');
    }

    // Volunteer scopes
    public function scopeVolunteers($query)
    {
        return $query->where('role', 'volunteer');
    }

    public function scopePendingVolunteers($query)
    {
        return $query->where('role', 'volunteer')->where('volunteer_status', 'pending');
    }

    public function scopeApprovedVolunteers($query)
    {
        return $query->where('role', 'volunteer')->where('volunteer_status', 'approved');
    }

    // Helper methods
    public function isVolunteer(): bool
    {
        return $this->role === 'volunteer';
    }

    public function isApprovedVolunteer(): bool
    {
        return $this->role === 'volunteer' && $this->volunteer_status === 'approved';
    }
}
