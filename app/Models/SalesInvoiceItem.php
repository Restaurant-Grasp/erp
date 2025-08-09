<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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


}
