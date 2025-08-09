<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSerialNumber extends Model
{
    protected $table = 'product_serial_numbers';

    protected $fillable = [
        'product_id',
        'serial_number',
        'purchase_date',
        'purchase_invoice_id',
        'warranty_start_date',
        'warranty_end_date',
        'current_status',
        'customer_id',
        'sales_invoice_id',
        'notes',
    ];

    // Timestamps enabled by default
}
