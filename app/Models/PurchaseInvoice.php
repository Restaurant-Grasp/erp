<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoice extends Model
{
    protected $fillable = [
        'invoice_no',
        'vendor_invoice_no',
        'invoice_date',
        'vendor_id',
        'po_id',
        'invoice_type',
        'currency',
        'exchange_rate',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'payment_terms',
        'due_date',
        'status',
        'paid_amount',
        'balance_amount',
        'received_amount',
        'received_percentage',
        'notes',
        'entry_id',
        'created_by'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'received_percentage' => 'decimal:2',
        'exchange_rate' => 'decimal:4'
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class, 'invoice_id');
    }

    public function grns(): HasMany
    {
        return $this->hasMany(GoodsReceiptNote::class, 'invoice_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    // Accessors
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'pending' => 'warning',
            'partial' => 'info',
            'paid' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'dark',
            default => 'secondary'
        };
    }

    public function getInvoiceTypeBadgeAttribute(): string
    {
        return match($this->invoice_type) {
            'direct' => 'primary',
            'po_conversion' => 'success',
            default => 'secondary'
        };
    }

    public function getDaysOverdueAttribute(): int
    {
        if ($this->status !== 'overdue' || !$this->due_date) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    // Scopes
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereIn('status', ['pending', 'partial']);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }
    public function payments()
	{
		return $this->hasMany(PurchaseInvoicePayment::class, 'invoice_id');
	}
	
	/**
	 * Get the files associated with the purchase invoice.
	 */
	public function files(): HasMany
	{
		return $this->hasMany(PurchaseInvoiceFile::class, 'invoice_id');
	}
}