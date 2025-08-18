<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMode extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'ledger_id',
        'description',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    /**
     * Get the ledger associated with this payment mode
     */
    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * Get sales payments using this payment mode
     */
    public function salesPayments()
    {
        return $this->hasMany(SalesInvoicePayment::class, 'payment_mode_id');
    }

    /**
     * Get purchase payments using this payment mode
     */
    public function purchasePayments()
    {
        return $this->hasMany(PurchaseInvoicePayment::class, 'payment_mode_id');
    }

    /**
     * Scope for active payment modes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for receipt type payment modes
     */
    public function scopeReceipt($query)
    {
        return $query->where('type', 'receipt');
    }

    /**
     * Scope for payment type payment modes
     */
    public function scopePayment($query)
    {
        return $query->where('type', 'payment');
    }

    /**
     * Get total amount received through this payment mode (sales)
     */
    public function getTotalReceivedAttribute()
    {
        return $this->salesPayments()->sum('paid_amount');
    }

    /**
     * Get total amount paid through this payment mode (purchases)
     */
    public function getTotalPaidAttribute()
    {
        return $this->purchasePayments()->sum('paid_amount');
    }

    /**
     * Check if payment mode can be deleted
     */
    public function getCanBeDeletedAttribute()
    {
        return $this->salesPayments()->count() === 0 && 
               $this->purchasePayments()->count() === 0;
    }

    /**
     * Get display name with type
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . ucfirst($this->type) . ')';
    }
    public function createdBy()
{
    return $this->belongsTo(User::class, 'created_by');
}

}