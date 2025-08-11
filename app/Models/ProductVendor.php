<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVendor extends Model
{
    protected $table = 'product_vendors';
    
    protected $fillable = [
        'product_id',
        'vendor_id',
        'vendor_product_code',
        'vendor_price',
        'lead_time_days',
        'is_preferred'
    ];

    protected $casts = [
        'vendor_price' => 'decimal:2',
        'lead_time_days' => 'integer',
        'is_preferred' => 'boolean'
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    // Accessors
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->vendor_price, 2);
    }

    public function getLeadTimeTextAttribute(): string
    {
        if ($this->lead_time_days <= 0) {
            return 'Same day';
        } elseif ($this->lead_time_days == 1) {
            return '1 day';
        } else {
            return $this->lead_time_days . ' days';
        }
    }

    public function getPreferredBadgeAttribute(): string
    {
        return $this->is_preferred ? 'success' : 'secondary';
    }

    // Scopes
    public function scopePreferred($query)
    {
        return $query->where('is_preferred', true);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeWithLowPrice($query)
    {
        return $query->orderBy('vendor_price', 'asc');
    }

    public function scopeWithFastDelivery($query)
    {
        return $query->orderBy('lead_time_days', 'asc');
    }

    // Methods
    public function calculateTotalPrice($quantity)
    {
        return $this->vendor_price * $quantity;
    }

    public function getEstimatedDeliveryDate($orderDate = null)
    {
        $orderDate = $orderDate ?: now();
        return $orderDate->addDays($this->lead_time_days);
    }

    public function isPriceBetterThan($otherPrice)
    {
        return $this->vendor_price < $otherPrice;
    }

    // Static methods
    public static function getBestPriceForProduct($productId)
    {
        return static::where('product_id', $productId)
                    ->orderBy('vendor_price', 'asc')
                    ->first();
    }

    public static function getPreferredVendorForProduct($productId)
    {
        return static::where('product_id', $productId)
                    ->where('is_preferred', true)
                    ->first();
    }

    public static function getVendorsForProduct($productId)
    {
        return static::with('vendor')
                    ->where('product_id', $productId)
                    ->orderBy('is_preferred', 'desc')
                    ->orderBy('vendor_price', 'asc')
                    ->get();
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        // Ensure only one preferred vendor per product
        static::saving(function ($productVendor) {
            if ($productVendor->is_preferred) {
                // Remove preferred status from other vendors for this product
                static::where('product_id', $productVendor->product_id)
                     ->where('id', '!=', $productVendor->id)
                     ->update(['is_preferred' => false]);
            }
        });
    }
}