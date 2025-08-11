<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_no',
        'po_date',
        'vendor_id',
        'reference_no',
        'delivery_date',
        'currency',
        'exchange_rate',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'total_received_amount',
        'received_percentage',
        'terms_conditions',
        'status',
        'approval_status',
        'approved_by',
        'approved_date',
        'approval_notes',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'po_date' => 'date',
        'delivery_date' => 'date',
        'approved_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_received_amount' => 'decimal:2',
        'received_percentage' => 'decimal:2',
        'exchange_rate' => 'decimal:4'
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'po_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class, 'po_id');
    }

    public function grns(): HasMany
    {
        return $this->hasMany(GoodsReceiptNote::class, 'po_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessors
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'pending_approval' => 'warning',
            'approved' => 'success',
            'partial' => 'info',
            'received' => 'primary',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getApprovalStatusBadgeAttribute(): string
    {
        return match($this->approval_status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    // Scopes
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }
}