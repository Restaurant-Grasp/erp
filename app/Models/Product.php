<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Categories;
use App\Models\Brand;
use App\Models\Uom;
use App\Models\Models;


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
   // Category Relationship
   public function category()
   {
       return $this->belongsTo(Categories::class, 'category_id');
   }

   // Brand Relationship
   public function brand()
   {
       return $this->belongsTo(Brand::class, 'brand_id');
   }

   // Model Relationship
   public function model()
   {
       return $this->belongsTo(Models::class, 'model_id');
   }

   // UOM Relationship
   public function uom()
   {
       return $this->belongsTo(Uom::class, 'uom_id');
   }
}
