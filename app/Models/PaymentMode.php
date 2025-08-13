<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMode extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ledger_id',
        'description',
        'status',
        'created_by'
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the ledger associated with the payment mode
     */
    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * Get the user who created the payment mode
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get sales invoice payments using this payment mode
     */
    public function salesPayments()
    {
        return $this->hasMany(SalesInvoicePayment::class);
    }

    /**
     * Get purchase invoice payments using this payment mode
     */
    public function purchasePayments()
    {
        return $this->hasMany(PurchaseInvoicePayment::class);
    }

    /**
     * Scope for active payment modes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get display name with ledger info
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ($this->ledger ? ' (' . $this->ledger->name . ')' : '');
    }
}