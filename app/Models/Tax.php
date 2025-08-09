<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use SoftDeletes;

    protected $table = 'taxes';

    protected $fillable = [
        'name',
        'applicable_for',
        'ledger_id',
        'percent',
        'status',
        'created_by'
    ];

    protected $casts = [
        'percent' => 'decimal:2',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the ledger associated with this tax
     */
    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * Get the user who created this tax
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active taxes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for product taxes
     */
    public function scopeForProducts($query)
    {
        return $query->whereIn('applicable_for', ['product', 'both']);
    }

    /**
     * Scope for service taxes
     */
    public function scopeForServices($query)
    {
        return $query->whereIn('applicable_for', ['service', 'both']);
    }

    /**
     * Calculate tax amount for given base amount
     */
    public function calculateTaxAmount($baseAmount)
    {
        return round(($baseAmount * $this->percent) / 100, 2);
    }

    /**
     * Get formatted tax display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->percent . '%)';
    }

    /**
     * Check if tax is applicable for given item type
     */
    public function isApplicableFor($itemType)
    {
        return in_array($this->applicable_for, [$itemType, 'both']);
    }
}