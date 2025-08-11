<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceiptNote extends Model
{
    protected $fillable = [
        'grn_no',
        'grn_date',
        'vendor_id',
        'po_id',
        'invoice_id',
        'received_by',
        'status',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'grn_date' => 'date'
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

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GrnItem::class, 'grn_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'received_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class, 'grn_id');
    }

    // Accessors
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'partial' => 'warning',
            'completed' => 'success',
            default => 'secondary'
        };
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items()->count();
    }

    public function getTotalQuantityAttribute(): float
    {
        return $this->items()->sum('quantity');
    }

    public function getTotalAcceptedQuantityAttribute(): float
    {
        return $this->items()->sum('accepted_quantity');
    }

    public function getTotalDamagedQuantityAttribute(): float
    {
        return $this->items()->sum('damaged_quantity');
    }
}