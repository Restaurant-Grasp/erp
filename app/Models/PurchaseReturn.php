<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'return_no',
        'return_date',
        'vendor_id',
        'grn_id',
        'invoice_id',
        'return_type',
        'total_amount',
        'status',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_amount' => 'decimal:2'
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptNote::class, 'grn_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class, 'return_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'returned' => 'primary',
            'credited' => 'success',
            default => 'secondary'
        };
    }

    public function getReturnTypeBadgeAttribute(): string
    {
        return match($this->return_type) {
            'damaged' => 'danger',
            'defective' => 'warning',
            'wrong_item' => 'info',
            'excess' => 'secondary',
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
}