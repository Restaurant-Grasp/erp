<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;
    
    protected $table = 'entries';
    
    protected $fillable = [
        'entrytype_id',
        'number',
        'date',
        'dr_total',
        'cr_total',
        'narration',
        'inv_id',
        'inv_type',
        'fund_id',
        'payment',
        'paid_to',
        'entry_code',
        'status',
        'cheque_no',
        'cheque_date',
        'return_date',
        'extra_charge',
        'collection_date',
        'created_by',
        'updated_at'
    ];
    
    protected $casts = [
        'date' => 'date',
        'cheque_date' => 'date',
        'return_date' => 'date',
        'collection_date' => 'date',
        'dr_total' => 'decimal:2',
        'cr_total' => 'decimal:2',
        'extra_charge' => 'decimal:2'
    ];
    
    /**
     * Get the entry type name
     */
    public function getEntryTypeNameAttribute()
    {
        $types = [
            1 => 'Receipt',
            2 => 'Payment',
            3 => 'Contra',
            4 => 'Journal',
            5 => 'Credit Note',
            6 => 'Inventory Journal'
        ];
        
        return $types[$this->entrytype_id] ?? 'Unknown';
    }
    
    /**
     * Get the entry items
     */
    public function entryItems()
    {
        return $this->hasMany(EntryItem::class, 'entry_id');
    }
    
    /**
     * Get the fund
     */
    public function fund()
    {
        return $this->belongsTo(Fund::class, 'fund_id');
    }
    
    /**
     * Get the creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get debit items
     */
    public function debitItems()
    {
        return $this->hasMany(EntryItem::class, 'entry_id')->where('dc', 'D');
    }
    
    /**
     * Get credit items
     */
    public function creditItems()
    {
        return $this->hasMany(EntryItem::class, 'entry_id')->where('dc', 'C');
    }
    
    /**
     * Check if entry is balanced
     */
    public function isBalanced()
    {
        return abs($this->dr_total - $this->cr_total) < 0.01;
    }
    
    /**
     * Get the payment status
     */
    public function getPaymentStatusAttribute()
    {
        if ($this->payment == 'CHEQUE') {
            if ($this->return_date) {
                return 'Returned';
            } elseif ($this->collection_date) {
                return 'Cleared';
            } else {
                return 'Pending';
            }
        }
        
        return 'Completed';
    }
}