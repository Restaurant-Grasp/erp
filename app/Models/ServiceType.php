<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceType extends Model
{
    protected $table = 'service_types';
    
    protected $fillable = [
        'name',
        'code',
        'description',
        'has_warranty',
        'has_maintenance',
        'is_subscription',
        'status',
        'created_by'
    ];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_service_types', 'service_type_id', 'customer_id');
    }

    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'vendor_service_types', 'service_type_id', 'vendor_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}