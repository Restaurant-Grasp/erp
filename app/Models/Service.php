<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $table = 'services';

    protected $fillable = [
        'service_type_id',
        'ledger_id',
        'name',
        'item_type',
        'code',
        'description',
        'base_price',
        'is_recurring',
        'billing_cycle',
        'status',
        'created_by',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_recurring' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Get the service type that owns the service
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * Get the ledger that owns the service
     */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * Scope for active services
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for recurring services
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', 1);
    }

    /**
     * Scope for one-time services
     */
    public function scopeOneTime($query)
    {
        return $query->where('is_recurring', 0);
    }

    /**
     * Scope for services by type
     */
    public function scopeByType($query, $serviceTypeId)
    {
        return $query->where('service_type_id', $serviceTypeId);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return 'RM ' . number_format($this->base_price, 2);
    }

    /**
     * Get billing cycle label
     */
    public function getBillingCycleLabelAttribute()
    {
        $labels = [
            'one-time' => 'One Time',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly'
        ];

        return $labels[$this->billing_cycle] ?? 'Unknown';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->status ? 'badge bg-success' : 'badge bg-danger';
    }

    /**
     * Get recurring label
     */
    public function getRecurringLabelAttribute()
    {
        return $this->is_recurring ? 'Yes' : 'No';
    }

    /**
     * Check if service can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Add your business logic here
        // Check if service is used in quotations, invoices, etc.
        return true;
    }
}