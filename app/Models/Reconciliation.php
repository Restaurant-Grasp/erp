<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reconciliation extends Model
{
    protected $table = 'reconciliations';
    
    protected $fillable = [
        'ledger_id',
        'month',
        'statement_closing_balance',
        'reconciled_balance',
        'opening_balance',
        'difference',
        'status',
        'reconciled_date',
        'reconciled_by',
        'notes',
        'created_by'
    ];
    
    protected $casts = [
        'statement_closing_balance' => 'decimal:2',
        'reconciled_balance' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'reconciled_date' => 'date'
    ];
    
    /**
     * Get the ledger this reconciliation is for
     */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }
    
    /**
     * Get the user who created this reconciliation
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the user who reconciled/finalized this
     */
    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }
    
    /**
     * Get adjustments for this reconciliation
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(ReconciliationAdjustment::class);
    }
    
    /**
     * Get reconciled items
     */
    public function reconciledItems(): HasMany
    {
        return $this->hasMany(EntryItem::class, 'reconciliation_id')
            ->where('is_reconciled', 1);
    }
    
    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => 'Draft',
            'completed' => 'Completed',
            'locked' => 'Locked'
        ];
        
        return $labels[$this->status] ?? $this->status;
    }
    
    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'draft' => 'warning',
            'completed' => 'success',
            'locked' => 'danger'
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }
    
    /**
     * Get month display
     */
    public function getMonthDisplayAttribute()
    {
        return date('F Y', strtotime($this->month . '-01'));
    }
    
    /**
     * Check if editable
     */
    public function getIsEditableAttribute()
    {
        return $this->status !== 'locked';
    }
}