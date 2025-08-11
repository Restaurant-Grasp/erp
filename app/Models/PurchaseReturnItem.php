<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnItem extends Model
{
    protected $fillable = [
        'return_id',
        'grn_item_id',
        'product_id',
        'serial_number_id',
        'quantity',
        'unit_price',
        'total_amount',
        'reason',
        'replacement_required',
        'replacement_po_no'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'replacement_required' => 'boolean'
    ];

    // Relationships
    public function return(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class, 'return_id');
    }

    public function grnItem(): BelongsTo
    {
        return $this->belongsTo(GrnItem::class, 'grn_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function serialNumber(): BelongsTo
    {
        return $this->belongsTo(ProductSerialNumber::class, 'serial_number_id');
    }
}