<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'po_id',
        'item_type',
        'item_id',
        'description',
        'quantity',
        'received_quantity',
        'remaining_quantity',
        'uom_id',
        'unit_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'sort_order'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    // Relationships
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id')->where('item_type', 'product');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'item_id')->where('item_type', 'service');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(UOM::class);
    }

    // Accessors
    public function getItemNameAttribute(): string
    {
        if ($this->item_type === 'product') {
            return $this->product?->name ?? 'Unknown Product';
        }
        return $this->service?->name ?? 'Unknown Service';
    }

    public function getReceivedPercentageAttribute(): float
    {
        return $this->quantity > 0 ? ($this->received_quantity / $this->quantity) * 100 : 0;
    }
}