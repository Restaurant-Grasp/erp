<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryItem extends Model
{
    protected $table = 'entryitems';
    
    protected $fillable = [
        'entry_id',
        'ledger_id',
        'details',
        'amount',
        'is_discount',
        'dc',
        'narration',
        'clearancemode',
        'agingmode',
        'reconciliation_date',
        'is_reconciled',
        'reconciliation_id',
        'flag_end',
        'quantity',
        'unit_price',
        'uom_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_discount' => 'boolean',
        'reconciliation_date' => 'date',
        'is_reconciled' => 'boolean',
        'flag_end' => 'boolean',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    /**
     * Get the entry this item belongs to
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class, 'entry_id');
    }

    /**
     * Get the ledger this item belongs to
     */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }

    /**
     * Get the unit of measure
     */
    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }

    /**
     * Get the reconciliation record
     */
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(Reconciliation::class, 'reconciliation_id');
    }

    /**
     * Check if this is a debit entry
     */
    public function isDebit(): bool
    {
        return strtoupper($this->dc) === 'D';
    }

    /**
     * Check if this is a credit entry
     */
    public function isCredit(): bool
    {
        return strtoupper($this->dc) === 'C';
    }

    /**
     * Get formatted amount with Dr/Cr indication
     */
    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2) . ' ' . ($this->isDebit() ? 'Dr' : 'Cr');
    }

    /**
     * Get signed amount (positive for debit, negative for credit)
     */
    public function getSignedAmount(): float
    {
        return $this->isDebit() ? $this->amount : -$this->amount;
    }

    /**
     * Check if this item is reconciled
     */
    public function isReconciled(): bool
    {
        return $this->is_reconciled;
    }

    /**
     * Check if this is a discount entry
     */
    public function isDiscount(): bool
    {
        return $this->is_discount;
    }

    /**
     * Get total value (quantity * unit_price)
     */
    public function getTotalValue(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Get formatted total value
     */
    public function getFormattedTotalValue(): string
    {
        return 'RM ' . number_format($this->getTotalValue(), 2);
    }

    /**
     * Get entry type description
     */
    public function getEntryTypeDescription(): string
    {
        if (!$this->entry) {
            return 'Unknown';
        }

        $types = [
            1 => 'Receipt',
            2 => 'Payment',
            3 => 'Contra',
            4 => 'Journal',
            5 => 'Credit Note',
            6 => 'Inventory Journal'
        ];

        return $types[$this->entry->entrytype_id] ?? 'Unknown';
    }

    /**
     * Scope to get debit entries
     */
    public function scopeDebits($query)
    {
        return $query->where('dc', 'D');
    }

    /**
     * Scope to get credit entries
     */
    public function scopeCredits($query)
    {
        return $query->where('dc', 'C');
    }

    /**
     * Scope to get reconciled entries
     */
    public function scopeReconciled($query)
    {
        return $query->where('is_reconciled', true);
    }

    /**
     * Scope to get unreconciled entries
     */
    public function scopeUnreconciled($query)
    {
        return $query->where('is_reconciled', false);
    }

    /**
     * Scope to get discount entries
     */
    public function scopeDiscounts($query)
    {
        return $query->where('is_discount', true);
    }

    /**
     * Scope to get entries for specific ledger
     */
    public function scopeForLedger($query, $ledgerId)
    {
        return $query->where('ledger_id', $ledgerId);
    }

    /**
     * Scope to get entries within date range
     */
    public function scopeDateRange($query, $fromDate, $toDate)
    {
        return $query->whereHas('entry', function($q) use ($fromDate, $toDate) {
            $q->whereBetween('date', [$fromDate, $toDate]);
        });
    }

    /**
     * Scope to get entries for specific entry type
     */
    public function scopeEntryType($query, $entryTypeId)
    {
        return $query->whereHas('entry', function($q) use ($entryTypeId) {
            $q->where('entrytype_id', $entryTypeId);
        });
    }

    /**
     * Mark as reconciled
     */
    public function markAsReconciled($reconciliationId = null, $reconciliationDate = null)
    {
        $this->update([
            'is_reconciled' => true,
            'reconciliation_id' => $reconciliationId,
            'reconciliation_date' => $reconciliationDate ?? now()->toDateString()
        ]);
    }

    /**
     * Mark as unreconciled
     */
    public function markAsUnreconciled()
    {
        $this->update([
            'is_reconciled' => false,
            'reconciliation_id' => null,
            'reconciliation_date' => null
        ]);
    }

    /**
     * Get aging days (for receivables/payables)
     */
    public function getAgingDays($asOnDate = null): int
    {
        $asOnDate = $asOnDate ?? now()->toDateString();
        $entryDate = $this->entry ? $this->entry->date : now()->toDateString();
        
        return \Carbon\Carbon::parse($entryDate)->diffInDays(\Carbon\Carbon::parse($asOnDate));
    }

    /**
     * Get aging bucket
     */
    public function getAgingBucket($asOnDate = null): string
    {
        $days = $this->getAgingDays($asOnDate);
        
        if ($days <= 30) {
            return 'current';
        } elseif ($days <= 60) {
            return '31-60';
        } elseif ($days <= 90) {
            return '61-90';
        } else {
            return 'over-90';
        }
    }

    /**
     * Check if entry has inventory component
     */
    public function hasInventory(): bool
    {
        return $this->quantity > 0 && $this->unit_price > 0;
    }

    /**
     * Get description for display
     */
    public function getDescription(): string
    {
        return $this->details ?: $this->narration ?: 'No description';
    }
}