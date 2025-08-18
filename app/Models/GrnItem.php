<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrnItem extends Model
{
    protected $fillable = [
        'grn_id',
        'po_item_id',
        'invoice_item_id',
        'product_id',
        'quantity',
        'damaged_quantity',
        'accepted_quantity',
        'uom_id',
        'serial_numbers',
        'expiry_date',
        'batch_no',
        'damage_reason',
        'replacement_required',
        'replacement_po_item_id',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'accepted_quantity' => 'decimal:2',
        'expiry_date' => 'date',
        'replacement_required' => 'boolean',
        'serial_numbers' => 'array'
    ];

    // Relationships
    public function grn(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptNote::class, 'grn_id');
    }

    public function poItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id');
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoiceItem::class, 'invoice_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(ProductSerialNumber::class, 'grn_item_id');
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class, 'grn_item_id');
    }

    // Accessors
    public function getDamagePercentageAttribute(): float
    {
        return $this->quantity > 0 ? ($this->damaged_quantity / $this->quantity) * 100 : 0;
    }

    public function getAcceptancePercentageAttribute(): float
    {
        return $this->quantity > 0 ? ($this->accepted_quantity / $this->quantity) * 100 : 0;
    }
}