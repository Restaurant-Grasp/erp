<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SalesInvoicePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_date',
        'paid_amount',
        'payment_mode_id',
        'received_by',
        'notes',
        'file_upload',
        'account_migration',
        'created_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'paid_amount' => 'decimal:2',
        'account_migration' => 'boolean'
    ];

    /**
     * Get the invoice that this payment belongs to
     */
    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'invoice_id');
    }

    /**
     * Get the payment mode
     */
    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class, 'payment_mode_id');
    }

    /**
     * Get the user who received the payment
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the user who created the payment record
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the accounting entry for this payment
     */
    public function entry()
    {
        return $this->hasOne(Entry::class, 'inv_id')->where('inv_type', 3);
    }

    /**
     * Get file URL if file exists
     */
    public function getFileUrlAttribute()
    {
        if ($this->file_upload) {
            return asset('storage/' . $this->file_upload);
        }
        return null;
    }

    /**
     * Check if payment can be edited
     */
    public function getCanBeEditedAttribute()
    {
        return !$this->account_migration;
    }

    /**
     * Check if payment can be deleted
     */
    public function getCanBeDeletedAttribute()
    {
        return !$this->account_migration;
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Update invoice totals when payment is saved
        static::saved(function ($payment) {
            $payment->updateInvoiceTotals();
        });

        // Update invoice totals when payment is deleted
        static::deleted(function ($payment) {
            $payment->updateInvoiceTotals();
        });
    }

    /**
     * Update invoice paid amount and balance
     */
    protected function updateInvoiceTotals()
    {
        if ($this->invoice) {
            $totalPaid = $this->invoice->payments()->sum('paid_amount');
            $balanceAmount = $this->invoice->total_amount - $totalPaid;

            // Determine status
            $status = 'pending';
            if ($totalPaid >= $this->invoice->total_amount) {
                $status = 'paid';
            } elseif ($totalPaid > 0) {
                $status = 'partial';
            } elseif ($this->invoice->due_date < now() && $balanceAmount > 0) {
                $status = 'overdue';
            }

            $this->invoice->update([
                'paid_amount' => $totalPaid,
                'balance_amount' => $balanceAmount,
                'status' => $status
            ]);
        }
    }

    /**
     * Scope for payments within date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope for payments by payment mode
     */
    public function scopeByPaymentMode($query, $paymentModeId)
    {
        return $query->where('payment_mode_id', $paymentModeId);
    }

    /**
     * Scope for migrated payments
     */
    public function scopeMigrated($query)
    {
        return $query->where('account_migration', 1);
    }

    /**
     * Scope for non-migrated payments
     */
    public function scopeNotMigrated($query)
    {
        return $query->where('account_migration', 0);
    }
}