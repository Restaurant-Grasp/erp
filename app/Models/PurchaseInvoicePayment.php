<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoicePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_date',
        'paid_amount',
        'payment_mode_id',
        'received_by',
        'file_upload',
        'notes',
        'account_migration',
        'created_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'paid_amount' => 'decimal:2',
        'account_migration' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the purchase invoice
     */
    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'invoice_id');
    }

    /**
     * Get the payment mode
     */
    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class);
    }

    /**
     * Get the user who received the payment
     */
    public function receivedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'received_by');
    }

    /**
     * Get the user who created the payment
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get file upload URL
     */
    public function getFileUrlAttribute()
    {
        return $this->file_upload ? asset('storage/payments/' . $this->file_upload) : null;
    }
}