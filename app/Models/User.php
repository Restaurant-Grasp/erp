<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'staff_id',
        'username',
        'name',
        'email',
        'password',
        'role_id', // Legacy field - will migrate to Spatie roles
        'is_active',
        'last_login',
        'login_attempts',
        'locked_until',
        'password_reset_token',
        'password_reset_expires',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'password_reset_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'locked_until' => 'datetime',
        'password_reset_expires' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];


    /**
     * Get the legacy role relationship
     * This is for backward compatibility
     */
    public function legacyRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the user who created this user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user's status badge
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_active 
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Inactive</span>';
    }


    /**
     * Check if user account is locked
     */
    public function isLocked()
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Lock user account for specified minutes
     */
    public function lockAccount($minutes = 30)
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes)
        ]);
    }

    /**
     * Unlock user account
     */
    public function unlockAccount()
    {
        $this->update([
            'locked_until' => null,
            'login_attempts' => 0
        ]);
    }

    /**
     * Increment login attempts
     */
    public function incrementLoginAttempts()
    {
        $this->increment('login_attempts');
        
        // Lock account after 5 failed attempts
        if ($this->login_attempts >= 5) {
            $this->lockAccount(30);
        }
    }

    /**
     * Reset login attempts
     */
    public function resetLoginAttempts()
    {
        $this->update(['login_attempts' => 0]);
    }

    /**
     * Update last login information
     */
    public function updateLastLogin($ip = null)
    {
        $this->update([
            'last_login' => now(),
            'login_attempts' => 0
        ]);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Override the username field for authentication
     * Can be either username or email
     */
    public function username()
    {
        return 'username';
    }
}