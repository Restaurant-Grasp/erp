<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'product_code',
        'ledger_id',
        'name',
        'description',
        'category_id',
        'brand_id',
        'model_id',
        'uom_id',
        'hsn_code',
        'barcode',
        'cost_price',
        'selling_price',
        'min_stock_level',
        'reorder_level',
        'has_serial_number',
        'has_warranty',
        'warranty_period_months',
        'is_active',
        'image',
        'created_by',
    ];
}
