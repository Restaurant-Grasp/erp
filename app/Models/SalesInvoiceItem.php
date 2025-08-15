<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoiceItem extends Model
{
    protected $table = 'sales_invoice_items';

    protected $fillable = [
        'invoice_id',
        'item_type',
        'item_id',
        'description',
        'quantity',
        'uom_id',
        'unit_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_id',
        'tax_rate',
        'tax_amount',
        'total_amount',
    ];

 /**
     * Relationship to Product (when item_type is 'product')
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    /**
     * Relationship to Service (when item_type is 'service')
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'item_id');
    }

    /**
     * Relationship to Package (when item_type is 'package')
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'item_id');
    }

    /**
     * Relationship to Tax
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }
     public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }
}
