<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageService extends Model
{
    protected $table = 'package_services';

    protected $fillable = [
        'package_id',
        'service_id',
        'product_id',
        'quantity',
        'amount',
        'item_type',
        'discount_percentage', // Individual item discount (usually 0 for your use case)
    ];

    protected $casts = [
        'quantity' => 'integer',
        'amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
    ];

    /**
     * Get the package that owns this item
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the service (if this is a service item)
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the product (if this is a product item)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the item (service or product) dynamically
     */
    public function getItemAttribute()
    {
        return $this->item_type === 'service' ? $this->service : $this->product;
    }

    /**
     * Get item name
     */
    public function getItemNameAttribute()
    {
        $item = $this->item;
        return $item ? $item->name : 'Unknown Item';
    }

    /**
     * Get item code
     */
    public function getItemCodeAttribute()
    {
        if ($this->item_type === 'service' && $this->service) {
            return $this->service->code;
        } elseif ($this->item_type === 'product' && $this->product) {
            return $this->product->product_code;
        }
        return 'N/A';
    }

    /**
     * Get item price (base price without any discounts)
     */
    public function getItemPriceAttribute()
    {
        if ($this->item_type === 'service' && $this->service) {
            return $this->service->base_price;
        } elseif ($this->item_type === 'product' && $this->product) {
            return $this->product->selling_price;
        }
        return 0;
    }

    /**
     * Get total price for this line item (quantity * unit price)
     * This is the subtotal before any package-level discount
     */
    public function getTotalPriceAttribute()
    {
        return $this->item_price * $this->quantity;
    }

    /**
     * Get individual item discount amount (usually 0 for your use case)
     */
    public function getItemDiscountAmountAttribute()
    {
        return ($this->total_price * $this->discount_percentage) / 100;
    }

    /**
     * Get line total after individual item discount
     */
    public function getLineTotalAttribute()
    {
        return $this->total_price - $this->item_discount_amount;
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalAttribute()
    {
        return 'RM ' . number_format($this->total_price, 2);
    }

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPriceAttribute()
    {
        return 'RM ' . number_format($this->item_price, 2);
    }

    /**
     * Get formatted line total
     */
    public function getFormattedLineTotalAttribute()
    {
        return 'RM ' . number_format($this->line_total, 2);
    }

    /**
     * Get item type badge class
     */
    public function getItemTypeBadgeClassAttribute()
    {
        return $this->item_type === 'service' ? 'badge bg-primary' : 'badge bg-info';
    }

    /**
     * Get item type label
     */
    public function getItemTypeLabelAttribute()
    {
        return ucfirst($this->item_type);
    }

    /**
     * Scope for services only
     */
    public function scopeServices($query)
    {
        return $query->where('item_type', 'service');
    }

    /**
     * Scope for products only
     */
    public function scopeProducts($query)
    {
        return $query->where('item_type', 'product');
    }

    /**
     * Get detailed item information
     */
    public function getDetailedInfoAttribute()
    {
        $item = $this->item;
        
        if (!$item) {
            return [
                'name' => 'Unknown Item',
                'code' => 'N/A',
                'description' => 'Item not found',
                'unit_price' => 0,
                'type' => $this->item_type,
            ];
        }

        $info = [
            'name' => $item->name,
            'unit_price' => $this->item_price,
            'type' => $this->item_type,
            'quantity' => $this->quantity,
            'total_price' => $this->total_price,
            'formatted_unit_price' => $this->formatted_unit_price,
            'formatted_total_price' => $this->formatted_total,
        ];

        if ($this->item_type === 'service') {
            $info['code'] = $item->code;
            $info['description'] = $item->description;
            $info['billing_cycle'] = $item->billing_cycle;
            $info['is_recurring'] = $item->is_recurring;
        } else {
            $info['code'] = $item->product_code;
            $info['description'] = $item->description;
            $info['category'] = $item->category->name ?? 'N/A';
            $info['brand'] = $item->brand->name ?? 'N/A';
        }

        return $info;
    }

    /**
     * Update the stored amount when quantity or related item price changes
     */
    public function updateAmount()
    {
        $newAmount = $this->total_price;
        
        if ($this->amount != $newAmount) {
            $this->update(['amount' => $newAmount]);
        }
        
        return $this;
    }

    /**
     * Ensure amount is always calculated correctly when saving
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            // Auto-calculate amount if not set
            if (is_null($model->amount) || $model->amount == 0) {
                $model->amount = $model->total_price;
            }
        });
    }
}