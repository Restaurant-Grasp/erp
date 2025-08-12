<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
      protected $table = 'quotation_items';
    protected $fillable = [
        'quotation_id',
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
        'sort_order'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sort_order' => 'integer'
    ];

    /**
     * Relationships
     */
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id')->where('item_type', 'product');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'item_id')->where('item_type', 'service');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'item_id')->where('item_type', 'package');
    }

    public function uom()
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    /**
     * Get item details based on type
     */
    public function getItemAttribute()
    {
        switch ($this->item_type) {
            case 'product':
                return $this->product;
            case 'service':
                return $this->service;
            case 'package':
                return $this->package;
            default:
                return null;
        }
    }

    public function getItemNameAttribute()
    {
        return $this->item ? $this->item->name : 'Unknown Item';
    }

    /**
     * Calculate amounts
     */
    public function calculateAmounts()
    {
        $lineTotal = $this->quantity * $this->unit_price;
        
        // Calculate discount
        if ($this->discount_type === 'percentage') {
            $this->discount_amount = ($lineTotal * $this->discount_value) / 100;
        } else {
            $this->discount_amount = $this->discount_value;
        }

        $discountedAmount = $lineTotal - $this->discount_amount;

        // Calculate tax (exclusive)
        if ($this->tax_id && $this->tax) {
            $this->tax_rate = $this->tax->percent;
            $this->tax_amount = ($discountedAmount * $this->tax_rate) / 100;
        } else {
            $this->tax_rate = 0;
            $this->tax_amount = 0;
        }

        $this->total_amount = $discountedAmount + $this->tax_amount;

        return $this;
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateAmounts();
        });

        static::saved(function ($item) {
            $item->quotation->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->quotation->calculateTotals();
        });
    }
}