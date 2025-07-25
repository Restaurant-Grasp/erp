<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'code',
        'description',
        'is_system',
        'status',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_system' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Get the user who created this role
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this is a system role
     */
    public function isSystem()
    {
        return $this->is_system || in_array($this->name, ['super_admin', 'admin']);
    }

    /**
     * Scope for active roles
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get users count attribute
     */
    public function getUsersCountAttribute()
    {
        return $this->users()->count();
    }

    /**
     * Get permissions count attribute
     */
    public function getPermissionsCountAttribute()
    {
        return $this->permissions()->count();
    }
}