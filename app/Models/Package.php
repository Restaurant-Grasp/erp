<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $table = 'packages';

    protected $fillable = [
        'name',
        'code',
        'description',
        'package_price',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'validity_days',
        'is_subscription',
        'subscription_cycle',
        'status',
        'ledger_id',
        'created_by',
    ];

    protected $casts = [
        'package_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'is_subscription' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Get the ledger that owns the package
     */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * Get all package items (services and products)
     */
    public function packageItems(): HasMany
    {
        return $this->hasMany(PackageService::class);
    }

    /**
     * Get package services only
     */
    public function services(): HasMany
    {
        return $this->hasMany(PackageService::class)->where('item_type', 'service');
    }

    /**
     * Get package products only
     */
    public function products(): HasMany
    {
        return $this->hasMany(PackageService::class)->where('item_type', 'product');
    }

    /**
     * Scope for active packages
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for subscription packages
     */
    public function scopeSubscription($query)
    {
        return $query->where('is_subscription', 1);
    }

    /**
     * Scope for one-time packages
     */
    public function scopeOneTime($query)
    {
        return $query->where('is_subscription', 0);
    }

    /**
     * Get formatted package price
     */
    public function getFormattedPriceAttribute()
    {
        return 'RM ' . number_format($this->package_price, 2);
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute()
    {
        return 'RM ' . number_format($this->subtotal ?? 0, 2);
    }

    /**
     * Get formatted discount amount
     */
    public function getFormattedDiscountAttribute()
    {
        return 'RM ' . number_format($this->discount_amount ?? 0, 2);
    }

    /**
     * Get subscription cycle label
     */
    public function getSubscriptionCycleLabelAttribute()
    {
        if (!$this->is_subscription || !$this->subscription_cycle) {
            return 'One Time';
        }

        $labels = [
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly', 
            'yearly' => 'Yearly'
        ];

        return $labels[$this->subscription_cycle] ?? 'Unknown';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->status ? 'badge bg-success' : 'badge bg-danger';
    }

    /**
     * Get validity label
     */
    public function getValidityLabelAttribute()
    {
        if (!$this->validity_days) {
            return 'Lifetime';
        }

        if ($this->validity_days >= 365) {
            $years = floor($this->validity_days / 365);
            $remaining = $this->validity_days % 365;
            $label = $years . ' year' . ($years > 1 ? 's' : '');
            
            if ($remaining > 0) {
                $label .= ' ' . $remaining . ' day' . ($remaining > 1 ? 's' : '');
            }
            
            return $label;
        } elseif ($this->validity_days >= 30) {
            $months = floor($this->validity_days / 30);
            $remaining = $this->validity_days % 30;
            $label = $months . ' month' . ($months > 1 ? 's' : '');
            
            if ($remaining > 0) {
                $label .= ' ' . $remaining . ' day' . ($remaining > 1 ? 's' : '');
            }
            
            return $label;
        } else {
            return $this->validity_days . ' day' . ($this->validity_days > 1 ? 's' : '');
        }
    }

    /**
     * Calculate total items count
     */
    public function getTotalItemsAttribute()
    {
        return $this->packageItems()->sum('quantity');
    }

    /**
     * Get package savings amount
     */
    public function getSavingsAttribute()
    {
        return $this->discount_amount ?? 0;
    }

    /**
     * Get package savings percentage
     */
    public function getSavingsPercentageAttribute()
    {
        return $this->discount_percentage ?? 0;
    }

    /**
     * Calculate individual item totals without package discount
     */
    public function getItemsSubtotalAttribute()
    {
        $total = 0;
        
        foreach ($this->packageItems as $item) {
            if ($item->item_type === 'service' && $item->service) {
                $total += $item->service->base_price * $item->quantity;
            } elseif ($item->item_type === 'product' && $item->product) {
                $total += $item->product->selling_price * $item->quantity;
            }
        }
        
        return $total;
    }

    /**
     * Get items with their details for display
     */
    public function getItemsWithDetailsAttribute()
    {
        return $this->packageItems->map(function ($item) {
            if ($item->item_type === 'service') {
                return [
                    'type' => 'service',
                    'id' => $item->service->id,
                    'name' => $item->service->name,
                    'code' => $item->service->code,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->service->base_price,
                    'total_price' => $item->service->base_price * $item->quantity,
                    'formatted_unit_price' => 'RM ' . number_format($item->service->base_price, 2),
                    'formatted_total_price' => 'RM ' . number_format($item->service->base_price * $item->quantity, 2),
                ];
            } elseif ($item->item_type === 'product') {
                return [
                    'type' => 'product',
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'code' => $item->product->product_code,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->product->selling_price,
                    'total_price' => $item->product->selling_price * $item->quantity,
                    'formatted_unit_price' => 'RM ' . number_format($item->product->selling_price, 2),
                    'formatted_total_price' => 'RM ' . number_format($item->product->selling_price * $item->quantity, 2),
                ];
            }
            return null;
        })->filter();
    }

    /**
     * Check if package can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Check if package is used in quotations, invoices, customer subscriptions, etc.
        // Add your business logic here
        return true;
    }

    /**
     * Recalculate package pricing based on current items and discount
     */
    public function recalculatePrice()
    {
        $subtotal = $this->items_subtotal;
        $discountAmount = ($subtotal * $this->discount_percentage) / 100;
        $finalPrice = $subtotal - $discountAmount;
        
        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'package_price' => $finalPrice
        ]);
        
        return $this;
    }

    /**
     * Apply discount to package
     */
    public function applyDiscount($percentage)
    {
        $percentage = max(0, min(100, $percentage)); // Ensure percentage is between 0 and 100
        $subtotal = $this->items_subtotal;
        $discountAmount = ($subtotal * $percentage) / 100;
        $finalPrice = $subtotal - $discountAmount;
        
        $this->update([
            'discount_percentage' => $percentage,
            'discount_amount' => $discountAmount,
            'package_price' => $finalPrice
        ]);
        
        return $this;
    }

    /**
     * Get detailed pricing breakdown
     */
    public function getPricingBreakdown()
    {
        $itemsSubtotal = $this->items_subtotal;
        $discountAmount = $this->discount_amount ?? 0;
        $finalPrice = $itemsSubtotal - $discountAmount;
        
        return [
            'items_subtotal' => $itemsSubtotal,
            'discount_percentage' => $this->discount_percentage ?? 0,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'savings' => $discountAmount,
            'formatted' => [
                'items_subtotal' => 'RM ' . number_format($itemsSubtotal, 2),
                'discount_amount' => 'RM ' . number_format($discountAmount, 2),
                'final_price' => 'RM ' . number_format($finalPrice, 2),
                'savings' => 'RM ' . number_format($discountAmount, 2),
            ]
        ];
    }

    /**
     * Update package items and recalculate pricing
     */
    public function updateItems(array $items)
    {
        // Delete existing items
        $this->packageItems()->delete();
        
        $subtotal = 0;
        
        // Add new items
        foreach ($items as $item) {
            $packageItem = $this->packageItems()->create([
                'service_id' => $item['item_type'] === 'service' ? $item['item_id'] : null,
                'product_id' => $item['item_type'] === 'product' ? $item['item_id'] : null,
                'item_type' => $item['item_type'],
                'quantity' => $item['quantity'],
                'amount' => 0, // Will be calculated
                'discount_percentage' => 0, // Individual items don't have discount
            ]);
            
            // Calculate item total
            if ($item['item_type'] === 'service') {
                $service = Service::find($item['item_id']);
                $itemTotal = $service->base_price * $item['quantity'];
            } else {
                $product = Product::find($item['item_id']);
                $itemTotal = $product->selling_price * $item['quantity'];
            }
            
            $packageItem->update(['amount' => $itemTotal]);
            $subtotal += $itemTotal;
        }
        
        // Recalculate package pricing
        $discountAmount = ($subtotal * $this->discount_percentage) / 100;
        $finalPrice = $subtotal - $discountAmount;
        
        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'package_price' => $finalPrice
        ]);
        
        return $this;
    }
}