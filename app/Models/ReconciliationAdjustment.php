<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconciliationAdjustment extends Model
{
    protected $table = 'reconciliation_adjustments';
    
    protected $fillable = [
        'reconciliation_id',
        'adjustment_type',
        'entry_id',
        'amount',
        'description',
        'created_by'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2'
    ];
    
    public $timestamps = false;
    
    /**
     * Get the reconciliation this adjustment belongs to
     */
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(Reconciliation::class);
    }
    
    /**
     * Get the entry created for this adjustment
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }
    
    /**
     * Get the user who created this adjustment
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get type label
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'manual_entry' => 'Manual Adjustment',
            'investigation_tag' => 'Investigation Tag'
        ];
        
        return $labels[$this->adjustment_type] ?? $this->adjustment_type;
    }
}